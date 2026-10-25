import { isProcessingStatus, pollImageProcessingStatuses } from './imageProcessingTracker';
import type { ProcessingStatus } from './imageProcessingTracker';

type ImageStatus = 'waiting' | 'uploading' | ProcessingStatus;

type UploadResponse = {
    image: {
        id: string;
        processing_status: ProcessingStatus;
    };
};

type UploadState = {
    file: File;
    imageId: string | null;
    loaded: number;
    status: ImageStatus;
    error: string | null;
    statusElement: HTMLElement;
};

type SelectedFile = {
    file: File;
    error: string | null;
};

const maximumConcurrentUploads = 4;

export function initializeImageLibraryUpload(): void {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll<HTMLElement>('.velor-image-library-upload').forEach(initializeUploader);
    });
}

function initializeUploader(container: HTMLElement): void {
    const form = container.querySelector<HTMLFormElement>('form');
    const input = container.querySelector<HTMLInputElement>('.image-upload-input');
    const dropzone = container.querySelector<HTMLElement>('.image-upload');
    const feedback = container.querySelector<HTMLElement>('.image-upload-feedback');
    const progressPanel = container.querySelector<HTMLElement>('.velor-image-upload-progress');
    const uploadLabel = container.querySelector<HTMLElement>('.velor-image-upload-label');
    const uploadBar = container.querySelector<HTMLElement>('.velor-image-upload-bar');
    const processingLabel = container.querySelector<HTMLElement>('.velor-image-processing-label');
    const processingBar = container.querySelector<HTMLElement>('.velor-image-processing-bar');
    const summary = container.querySelector<HTMLElement>('.velor-image-upload-summary');
    const fileList = container.querySelector<HTMLElement>('.velor-image-upload-files');
    const actions = container.querySelector<HTMLElement>('.velor-image-upload-actions');

    if (! form || ! input || ! dropzone || ! feedback || ! progressPanel || ! uploadLabel
        || ! uploadBar || ! processingLabel || ! processingBar || ! summary || ! fileList || ! actions) {
        return;
    }

    let active = false;

    const startUpload = (files: File[]): void => {
        if (active || files.length === 0) {
            return;
        }

        resetProgress(progressPanel, uploadLabel, uploadBar, processingLabel, processingBar, summary, fileList, actions);

        const states = selectFiles(files, dropzone, feedback)
            .map(selectedFile => createState(selectedFile, fileList, container));

        progressPanel.classList.remove('d-none');
        updateUploadProgress(states, container, uploadLabel, uploadBar);
        active = true;
        input.disabled = true;

        void (async (): Promise<void> => {
            try {
                await uploadFiles(
                    form,
                    input.name,
                    dropzone.dataset.attribute ?? 'upload',
                    states,
                    container,
                    uploadLabel,
                    uploadBar,
                );

                const processableStates = states.filter(
                    (state): state is UploadState & {imageId: string} => state.imageId !== null,
                );

                if (processableStates.length > 0) {
                    await trackProcessing(
                        form,
                        processableStates,
                        container,
                        processingLabel,
                        processingBar,
                    );
                }

                showResultSummary(states, container, summary);
            } catch {
                summary.textContent = container.dataset.processingError ?? '';
                summary.classList.add('text-danger');
                summary.classList.remove('text-success');
            } finally {
                active = false;
                input.disabled = false;
                input.value = '';
                actions.classList.remove('d-none');
            }
        })();
    };

    input.addEventListener('change', event => {
        event.stopImmediatePropagation();

        startUpload(Array.from(input.files ?? []));
    }, {capture: true});

    dropzone.addEventListener('drop', event => {
        event.preventDefault();
        event.stopImmediatePropagation();

        startUpload(Array.from(event.dataTransfer?.files ?? []));
    }, {capture: true});

    window.addEventListener('beforeunload', event => {
        if (active) {
            event.preventDefault();
        }
    });

    form.addEventListener('submit', event => {
        event.preventDefault();
    });
}

function selectFiles(files: File[], dropzone: HTMLElement, feedback: HTMLElement): SelectedFile[] {
    const maximumSize = Number.parseInt(dropzone.dataset.maximumSize ?? '5', 10) * 1024 * 1024;
    const rejectedCount = files.filter(file => file.size > maximumSize).length;

    feedback.textContent = rejectedCount === 0
        ? ''
        : tooLargeMessage(dropzone, rejectedCount);

    return files.map(file => ({
        file,
        error: file.size > maximumSize ? dropzone.dataset.imageTooLarge ?? '' : null,
    }));
}

async function uploadFiles(
    form: HTMLFormElement,
    inputName: string,
    attribute: string,
    states: UploadState[],
    container: HTMLElement,
    uploadLabel: HTMLElement,
    uploadBar: HTMLElement,
): Promise<void> {
    const uploadableStates = states.filter(state => state.status === 'waiting');
    let nextIndex = 0;

    const uploadNext = async (): Promise<void> => {
        while (nextIndex < uploadableStates.length) {
            const state = uploadableStates[nextIndex++];

            await uploadState(form, inputName, attribute, state, states, container, uploadLabel, uploadBar);
        }
    };

    await Promise.all(
        Array.from(
            {length: Math.min(maximumConcurrentUploads, uploadableStates.length)},
            () => uploadNext(),
        ),
    );
}

async function uploadState(
    form: HTMLFormElement,
    inputName: string,
    attribute: string,
    state: UploadState,
    states: UploadState[],
    container: HTMLElement,
    uploadLabel: HTMLElement,
    uploadBar: HTMLElement,
): Promise<void> {
    state.status = 'uploading';
    updateState(state, container);

    try {
        const response = await uploadFile(
            form,
            inputName,
            attribute,
            state.file,
            container.dataset.genericError ?? '',
            loaded => {
                state.loaded = loaded;
                updateUploadProgress(states, container, uploadLabel, uploadBar);
            },
        );

        state.imageId = response.image.id;
        state.status = response.image.processing_status;
    } catch (error) {
        state.status = 'failed';
        state.error = error instanceof Error ? error.message : container.dataset.genericError ?? '';
    } finally {
        state.loaded = state.file.size;
        updateState(state, container);
        updateUploadProgress(states, container, uploadLabel, uploadBar);
    }
}

function uploadFile(
    form: HTMLFormElement,
    inputName: string,
    attribute: string,
    file: File,
    genericError: string,
    onProgress: (loaded: number) => void,
): Promise<UploadResponse> {
    return new Promise((resolve, reject) => {
        const request = new XMLHttpRequest();
        const formData = new FormData(form);

        formData.delete(inputName);
        formData.delete(`${attribute}[order][]`);
        formData.append(inputName, file, file.name);
        formData.append(`${attribute}[order][]`, 'upload:0');

        request.open('POST', form.action);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.upload.addEventListener('progress', event => {
            if (event.lengthComputable) {
                onProgress((event.loaded / event.total) * file.size);
            }
        });
        request.addEventListener('load', () => {
            const response = parseJson(request.responseText);

            if (request.status >= 200 && request.status < 300 && isUploadResponse(response)) {
                resolve(response);

                return;
            }

            reject(new Error(errorMessage(response, genericError)));
        });
        request.addEventListener('error', () => reject(new Error(genericError)));
        request.send(formData);
    });
}

async function trackProcessing(
    form: HTMLFormElement,
    states: Array<UploadState & {imageId: string}>,
    container: HTMLElement,
    processingLabel: HTMLElement,
    processingBar: HTMLElement,
): Promise<void> {
    updateProcessingProgress(states, container, processingLabel, processingBar);

    await pollImageProcessingStatuses(
        states.filter(isProcessing).map(state => state.imageId),
        container.dataset.processingUrl ?? '',
        form.querySelector<HTMLInputElement>('input[name="_token"]')?.value ?? '',
        statuses => {
            states.forEach(state => {
                const status = statuses.get(state.imageId);

                if (status) {
                    state.status = status;
                    updateState(state, container);
                }
            });

            updateProcessingProgress(states, container, processingLabel, processingBar);
        },
    );
}

function createState(selectedFile: SelectedFile, fileList: HTMLElement, container: HTMLElement): UploadState {
    const item = document.createElement('li');
    const filename = document.createElement('span');
    const status = document.createElement('span');

    item.className = 'list-group-item d-flex justify-content-between gap-3';
    filename.className = 'velor-image-upload-filename';
    filename.textContent = selectedFile.file.name;
    status.className = 'velor-image-upload-file-status';
    item.append(filename, status);
    fileList.append(item);

    const state: UploadState = {
        file: selectedFile.file,
        imageId: null,
        loaded: selectedFile.error === null ? 0 : selectedFile.file.size,
        status: selectedFile.error === null ? 'waiting' : 'failed',
        error: selectedFile.error,
        statusElement: status,
    };

    updateState(state, container);

    return state;
}

function updateState(state: UploadState, container: HTMLElement): void {
    const key = `status${state.status.charAt(0).toUpperCase()}${state.status.slice(1)}`;
    const label = container.dataset[key] ?? state.status;

    state.statusElement.textContent = state.error ? `${label}: ${state.error}` : label;
    state.statusElement.classList.toggle('text-danger', state.status === 'failed');
    state.statusElement.classList.toggle('text-success', state.status === 'completed');
}

function updateUploadProgress(
    states: UploadState[],
    container: HTMLElement,
    uploadLabel: HTMLElement,
    uploadBar: HTMLElement,
): void {
    const totalBytes = states.reduce((total, state) => total + state.file.size, 0);
    const loadedBytes = states.reduce((total, state) => total + state.loaded, 0);
    const completed = states.filter(state => state.imageId !== null || state.status === 'failed').length;
    const percentage = totalBytes === 0 ? 100 : Math.round((loadedBytes / totalBytes) * 100);

    uploadLabel.textContent = replacePlaceholders(container.dataset.progressLabel ?? '', {
        completed: String(completed),
        total: String(states.length),
    });
    setProgress(uploadBar, percentage);
}

function updateProcessingProgress(
    states: UploadState[],
    container: HTMLElement,
    processingLabel: HTMLElement,
    processingBar: HTMLElement,
): void {
    const completed = states.filter(state => state.status === 'completed' || state.status === 'failed').length;
    const percentage = states.length === 0 ? 100 : Math.round((completed / states.length) * 100);

    processingLabel.textContent = replacePlaceholders(container.dataset.processingProgressLabel ?? '', {
        completed: String(completed),
        total: String(states.length),
    });
    setProgress(processingBar, percentage);
}

function showResultSummary(states: UploadState[], container: HTMLElement, summary: HTMLElement): void {
    const completed = states.filter(state => state.status === 'completed').length;
    const failed = states.filter(state => state.status === 'failed').length;
    let template = container.dataset.resultComplete ?? '';

    if (completed === 0) {
        template = container.dataset.resultFailed ?? '';
    } else if (failed > 0) {
        template = container.dataset.resultPartial ?? '';
    }

    summary.textContent = replacePlaceholders(template, {
        completed: String(completed),
        failed: String(failed),
    });
    setSummaryState(summary, failed > 0);
}

function setSummaryState(summary: HTMLElement, failed: boolean): void {
    summary.classList.toggle('text-danger', failed);
    summary.classList.toggle('text-success', ! failed);
}

function setProgress(progressBar: HTMLElement, percentage: number): void {
    const normalizedPercentage = Math.max(0, Math.min(100, percentage));

    progressBar.style.width = `${normalizedPercentage}%`;
    progressBar.parentElement?.setAttribute('aria-valuenow', String(normalizedPercentage));
}

function resetProgress(
    progressPanel: HTMLElement,
    uploadLabel: HTMLElement,
    uploadBar: HTMLElement,
    processingLabel: HTMLElement,
    processingBar: HTMLElement,
    summary: HTMLElement,
    fileList: HTMLElement,
    actions: HTMLElement,
): void {
    progressPanel.classList.add('d-none');
    uploadLabel.textContent = '';
    processingLabel.textContent = '';
    setProgress(uploadBar, 0);
    setProgress(processingBar, 0);
    summary.textContent = '';
    summary.classList.remove('text-danger', 'text-success');
    fileList.replaceChildren();
    actions.classList.add('d-none');
}

function tooLargeMessage(dropzone: HTMLElement, rejectedCount: number): string {
    const template = rejectedCount === 1
        ? dropzone.dataset.imageTooLarge ?? ''
        : dropzone.dataset.imagesTooLarge ?? '';

    return template.replace(':count', String(rejectedCount));
}

function parseJson(responseText: string): unknown {
    try {
        return JSON.parse(responseText) as unknown;
    } catch {
        return null;
    }
}

function isUploadResponse(response: unknown): response is UploadResponse {
    if (! response || typeof response !== 'object' || ! ('image' in response)) {
        return false;
    }

    const image = response.image;

    return Boolean(image && typeof image === 'object' && 'id' in image && typeof image.id === 'string'
        && 'processing_status' in image && isProcessingStatus(image.processing_status));
}

function isProcessing(state: UploadState): state is UploadState & {imageId: string} {
    return state.imageId !== null && (state.status === 'pending' || state.status === 'processing');
}

function errorMessage(response: unknown, genericError: string): string {
    if (response && typeof response === 'object' && 'errors' in response && response.errors
        && typeof response.errors === 'object') {
        for (const messages of Object.values(response.errors)) {
            if (Array.isArray(messages) && typeof messages[0] === 'string') {
                return messages[0];
            }
        }
    }

    if (response && typeof response === 'object' && 'message' in response && typeof response.message === 'string') {
        return response.message;
    }

    return genericError;
}

function replacePlaceholders(template: string, values: Record<string, string>): string {
    return Object.entries(values).reduce(
        (value, [key, replacement]) => value.replace(`:${key}`, replacement),
        template,
    );
}
