type UploadStatus = 'waiting' | 'uploading' | 'uploaded' | 'failed';

type UploadResponse = {
    image: {
        id: string;
        processing_status: string;
    };
};

type UploadState = {
    file: File;
    loaded: number;
    status: UploadStatus;
    error: string | null;
    statusElement: HTMLElement;
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
    const progressLabel = container.querySelector<HTMLElement>('.velor-image-upload-label');
    const progressBar = container.querySelector<HTMLElement>('.velor-image-upload-bar');
    const summary = container.querySelector<HTMLElement>('.velor-image-upload-summary');
    const fileList = container.querySelector<HTMLElement>('.velor-image-upload-files');

    if (! form || ! input || ! dropzone || ! feedback || ! progressPanel || ! progressLabel
        || ! progressBar || ! summary || ! fileList) {
        return;
    }

    let selectedFiles: File[] = [];
    let active = false;

    input.addEventListener('change', event => {
        event.stopImmediatePropagation();

        if (active) {
            return;
        }

        selectedFiles = selectFiles(Array.from(input.files ?? []), dropzone, feedback);
        resetProgress(progressPanel, progressLabel, progressBar, summary, fileList);
    }, {capture: true});

    dropzone.addEventListener('drop', event => {
        event.preventDefault();
        event.stopImmediatePropagation();

        if (active) {
            return;
        }

        selectedFiles = selectFiles(Array.from(event.dataTransfer?.files ?? []), dropzone, feedback);
        resetProgress(progressPanel, progressLabel, progressBar, summary, fileList);
    }, {capture: true});

    window.addEventListener('beforeunload', event => {
        if (active) {
            event.preventDefault();
        }
    });

    form.addEventListener('submit', event => {
        event.preventDefault();

        if (active || selectedFiles.length === 0) {
            return;
        }

        active = true;
        input.disabled = true;
        setSubmitDisabled(form, true);
        progressPanel.classList.remove('d-none');

        const states = selectedFiles.map(file => createState(file, fileList, container));

        void (async (): Promise<void> => {
            try {
                await uploadFiles(
                    form,
                    input.name,
                    dropzone.dataset.attribute ?? 'upload',
                    states,
                    container,
                    progressLabel,
                    progressBar,
                );
                showSummary(states, container, summary);
            } finally {
                active = false;
                selectedFiles = [];
                input.disabled = false;
                input.value = '';
                setSubmitDisabled(form, false);
            }
        })();
    });
}

function selectFiles(files: File[], dropzone: HTMLElement, feedback: HTMLElement): File[] {
    const maximumSize = Number.parseInt(dropzone.dataset.maximumSize ?? '5', 10) * 1024 * 1024;
    const selectedFiles = files.filter(file => file.size <= maximumSize);
    const rejectedCount = files.length - selectedFiles.length;

    feedback.textContent = rejectedCount === 0
        ? ''
        : tooLargeMessage(dropzone, rejectedCount);

    return selectedFiles;
}

async function uploadFiles(
    form: HTMLFormElement,
    inputName: string,
    attribute: string,
    states: UploadState[],
    container: HTMLElement,
    progressLabel: HTMLElement,
    progressBar: HTMLElement,
): Promise<void> {
    let nextIndex = 0;

    const uploadNext = async (): Promise<void> => {
        while (nextIndex < states.length) {
            const state = states[nextIndex++];

            await uploadState(form, inputName, attribute, state, states, container, progressLabel, progressBar);
        }
    };

    await Promise.all(
        Array.from(
            {length: Math.min(maximumConcurrentUploads, states.length)},
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
    progressLabel: HTMLElement,
    progressBar: HTMLElement,
): Promise<void> {
    state.status = 'uploading';
    updateState(state, container);

    try {
        await uploadFile(form, inputName, attribute, state.file, container.dataset.genericError ?? '', loaded => {
            state.loaded = loaded;
            updateProgress(states, container, progressLabel, progressBar);
        });
        state.status = 'uploaded';
    } catch (error) {
        state.status = 'failed';
        state.error = error instanceof Error ? error.message : container.dataset.genericError ?? '';
    } finally {
        state.loaded = state.file.size;
        updateState(state, container);
        updateProgress(states, container, progressLabel, progressBar);
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

function createState(file: File, fileList: HTMLElement, container: HTMLElement): UploadState {
    const item = document.createElement('li');
    const filename = document.createElement('span');
    const status = document.createElement('span');

    item.className = 'list-group-item d-flex justify-content-between gap-3';
    filename.className = 'text-truncate';
    filename.textContent = file.name;
    status.className = 'text-nowrap';
    item.append(filename, status);
    fileList.append(item);

    const state: UploadState = {
        file,
        loaded: 0,
        status: 'waiting',
        error: null,
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
    state.statusElement.classList.toggle('text-success', state.status === 'uploaded');
}

function updateProgress(
    states: UploadState[],
    container: HTMLElement,
    progressLabel: HTMLElement,
    progressBar: HTMLElement,
): void {
    const totalBytes = states.reduce((total, state) => total + state.file.size, 0);
    const loadedBytes = states.reduce((total, state) => total + state.loaded, 0);
    const completed = states.filter(state => state.status === 'uploaded' || state.status === 'failed').length;
    const percentage = totalBytes === 0 ? 100 : Math.round((loadedBytes / totalBytes) * 100);

    progressLabel.textContent = replacePlaceholders(container.dataset.progressLabel ?? '', {
        completed: String(completed),
        total: String(states.length),
    });
    progressBar.style.width = `${percentage}%`;
    progressBar.parentElement?.setAttribute('aria-valuenow', String(percentage));
}

function showSummary(states: UploadState[], container: HTMLElement, summary: HTMLElement): void {
    const failed = states.some(state => state.status === 'failed');

    summary.textContent = failed
        ? container.dataset.failedLabel ?? ''
        : container.dataset.completeLabel ?? '';
    summary.classList.toggle('text-danger', failed);
    summary.classList.toggle('text-success', ! failed);
}

function resetProgress(
    progressPanel: HTMLElement,
    progressLabel: HTMLElement,
    progressBar: HTMLElement,
    summary: HTMLElement,
    fileList: HTMLElement,
): void {
    progressPanel.classList.add('d-none');
    progressLabel.textContent = '';
    progressBar.style.width = '0%';
    progressBar.parentElement?.setAttribute('aria-valuenow', '0');
    summary.textContent = '';
    summary.classList.remove('text-danger', 'text-success');
    fileList.replaceChildren();
}

function setSubmitDisabled(form: HTMLFormElement, disabled: boolean): void {
    form.querySelectorAll<HTMLButtonElement>('button[type="submit"]').forEach(button => {
        button.disabled = disabled;
    });
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

    return Boolean(image && typeof image === 'object' && 'id' in image && typeof image.id === 'string');
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
