import type {
    ImagePickerContext,
    ImagePickerImage,
    ImagePickerLabels,
    ImagePickerResponse,
    ImagePickerSelection,
    ImagePickerUploadResponse,
} from './types';

const endpoint = '/cms/images/picker';
const uploadEndpoint = '/cms/images/picker';
let pickerDataPromise: Promise<ImagePickerResponse> | null = null;

export function preloadImagePicker(): Promise<ImagePickerResponse> {
    pickerDataPromise ??= fetchPickerData().catch(error => {
        pickerDataPromise = null;

        throw error;
    });

    return pickerDataPromise;
}

export async function openImagePicker(
    context: ImagePickerContext,
    accept?: (selection: ImagePickerSelection) => boolean,
): Promise<ImagePickerSelection | null> {
    const data = await preloadImagePicker();

    return new ImagePickerDialog(data.labels, context, accept).choose(data.images);
}

async function fetchPickerData(): Promise<ImagePickerResponse> {
    const response = await fetch(endpoint, {
        headers: {
            Accept: 'application/json',
        },
    });

    if (! response.ok) {
        throw new Error('Image picker request failed with status ' + response.status + '.');
    }

    return await response.json() as ImagePickerResponse;
}

class ImagePickerDialog {
    private readonly modal: HTMLElement;

    private readonly grid: HTMLElement;

    private readonly empty: HTMLElement;

    private readonly formatGroup: HTMLElement;

    private readonly formatSelect: HTMLSelectElement;

    private readonly altGroup: HTMLElement;

    private readonly altInput: HTMLInputElement;

    private readonly selectButton: HTMLButtonElement;

    private readonly uploadInput: HTMLInputElement;

    private readonly uploadLabel: HTMLElement;

    private readonly uploadFeedback: HTMLElement;

    private readonly previouslyFocused: HTMLElement | null;

    private images: ImagePickerImage[] = [];

    private selectedImage: ImagePickerImage | null = null;

    private resolveSelection: ((selection: ImagePickerSelection | null) => void) | null = null;

    public constructor(
        private readonly labels: ImagePickerLabels,
        private readonly context: ImagePickerContext,
        private readonly accept?: (selection: ImagePickerSelection) => boolean,
    ) {
        this.previouslyFocused = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
        this.modal = this.createModal();
        this.grid = this.requiredElement('.velor-image-picker-grid');
        this.empty = this.requiredElement('.velor-image-picker-empty');
        this.formatGroup = this.requiredElement('.velor-image-picker-format-group');
        this.formatSelect = this.requiredElement<HTMLSelectElement>('.velor-image-picker-format');
        this.altGroup = this.requiredElement('.velor-image-picker-alt-group');
        this.altInput = this.requiredElement<HTMLInputElement>('.velor-image-picker-alt');
        this.selectButton = this.requiredElement<HTMLButtonElement>('.velor-image-picker-select');
        this.uploadInput = this.requiredElement<HTMLInputElement>('.velor-image-picker-upload-input');
        this.uploadLabel = this.requiredElement('.velor-image-picker-upload-label');
        this.uploadFeedback = this.requiredElement('.velor-image-picker-upload-feedback');
    }

    public choose(images: ImagePickerImage[]): Promise<ImagePickerSelection | null> {
        this.images = images;
        this.renderImages(images);
        document.body.append(this.modal);
        document.body.classList.add('modal-open');
        this.requiredElement<HTMLInputElement>('.velor-image-picker-search').focus();

        return new Promise(resolve => {
            this.resolveSelection = resolve;
        });
    }

    private createModal(): HTMLElement {
        const modal = document.createElement('div');
        modal.className = 'modal velor-image-picker show';
        modal.tabIndex = -1;
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-label', this.labels.title);
        modal.innerHTML = [
            '<div class="modal-dialog modal-xl modal-dialog-scrollable">',
            '<div class="modal-content shadow">',
            '<div class="modal-header">',
            '<h5 class="modal-title"></h5>',
            '<button type="button" class="btn-close velor-image-picker-close"></button>',
            '</div>',
            '<div class="modal-body">',
            '<div class="velor-image-picker-tools mb-3">',
            '<input type="search" class="form-control velor-image-picker-search">',
            '<label class="btn btn-outline-primary velor-image-picker-upload-label">',
            '<i class="bi bi-upload me-1"></i><span></span>',
            '<input type="file" class="d-none velor-image-picker-upload-input" accept="image/*">',
            '</label>',
            '</div>',
            '<p class="velor-image-picker-upload-feedback text-danger d-none"></p>',
            '<div class="velor-image-picker-grid"></div>',
            '<p class="velor-image-picker-empty text-body-secondary mb-0 d-none"></p>',
            '</div>',
            '<div class="modal-footer align-items-end">',
            '<div class="velor-image-picker-format-group flex-grow-1 d-none">',
            '<label class="form-label"></label>',
            '<select class="form-select velor-image-picker-format"></select>',
            '</div>',
            '<div class="velor-image-picker-alt-group flex-grow-1 d-none">',
            '<label class="form-label"></label>',
            '<input type="text" class="form-control velor-image-picker-alt">',
            '</div>',
            '<button type="button" class="btn btn-secondary velor-image-picker-close"></button>',
            '<button type="button" class="btn btn-primary velor-image-picker-select" disabled></button>',
            '</div>',
            '</div>',
            '</div>',
        ].join('');

        const title = this.requiredElementFrom(modal, '.modal-title');
        const search = this.requiredElementFrom<HTMLInputElement>(modal, '.velor-image-picker-search');
        const empty = this.requiredElementFrom(modal, '.velor-image-picker-empty');
        const formatLabel = this.requiredElementFrom(modal, '.velor-image-picker-format-group label');
        const altLabel = this.requiredElementFrom(modal, '.velor-image-picker-alt-group label');
        const select = this.requiredElementFrom<HTMLButtonElement>(modal, '.velor-image-picker-select');
        const uploadInput = this.requiredElementFrom<HTMLInputElement>(modal, '.velor-image-picker-upload-input');
        const uploadLabel = this.requiredElementFrom(modal, '.velor-image-picker-upload-label');
        const uploadText = this.requiredElementFrom(modal, '.velor-image-picker-upload-label span');

        title.textContent = this.labels.title;
        search.placeholder = this.labels.search;
        search.setAttribute('aria-label', this.labels.search);
        empty.textContent = this.labels.empty;
        formatLabel.textContent = this.labels.format;
        altLabel.textContent = this.labels.alt;
        select.textContent = this.labels.select;
        uploadText.textContent = this.labels.upload;
        uploadLabel.classList.toggle('d-none', this.context !== 'rich-text');

        modal.querySelectorAll<HTMLButtonElement>('.velor-image-picker-close').forEach(button => {
            button.setAttribute('aria-label', this.labels.close);

            if (! button.classList.contains('btn-close')) {
                button.textContent = this.labels.close;
            }

            button.addEventListener('click', () => this.close(null));
        });
        search.addEventListener('input', () => this.filterImages(search.value));
        select.addEventListener('click', () => this.confirm());
        uploadInput.addEventListener('change', () => {
            const file = uploadInput.files?.item(0);

            if (file !== null && file !== undefined) {
                void this.upload(file);
            }
        });
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                this.close(null);
            }
        });
        modal.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                this.close(null);
            }
        });

        return modal;
    }

    private async upload(file: File): Promise<void> {
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        const formData = new FormData();

        formData.append('image', file);
        this.uploadInput.disabled = true;
        this.uploadLabel.classList.add('disabled');
        this.uploadFeedback.textContent = this.labels.uploading;
        this.uploadFeedback.classList.remove('d-none', 'text-danger');
        this.uploadFeedback.classList.add('text-body-secondary');

        try {
            const response = await fetch(uploadEndpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    ...(csrfToken === undefined ? {} : {'X-CSRF-TOKEN': csrfToken}),
                },
                body: formData,
            });

            if (! response.ok) {
                throw new Error('Image upload failed with status ' + response.status + '.');
            }

            const data = await response.json() as ImagePickerUploadResponse;

            this.images.unshift(data.image);
            this.uploadFeedback.classList.add('d-none');
            this.selectImage(data.image);
        } catch {
            this.uploadFeedback.textContent = this.labels.upload_failed;
            this.uploadFeedback.classList.remove('d-none', 'text-body-secondary');
            this.uploadFeedback.classList.add('text-danger');
        } finally {
            this.uploadInput.value = '';
            this.uploadInput.disabled = false;
            this.uploadLabel.classList.remove('disabled');
        }
    }

    private filterImages(search: string): void {
        const normalizedSearch = search.trim().toLocaleLowerCase();

        if (normalizedSearch === '') {
            this.renderImages(this.images);

            return;
        }

        this.renderImages(this.images.filter(image => (
            [image.name, image.filename].join(' ').toLocaleLowerCase().includes(normalizedSearch)
        )));
    }

    private renderImages(images: ImagePickerImage[]): void {
        this.grid.replaceChildren(...images.map(image => this.imageButton(image)));
        this.empty.classList.toggle('d-none', images.length !== 0);
    }

    private imageButton(image: ImagePickerImage): HTMLButtonElement {
        const button = document.createElement('button');
        const preview = document.createElement('img');
        const name = document.createElement('span');
        const filename = document.createElement('small');
        const hasFormats = Object.keys(image.formats).length !== 0;

        button.type = 'button';
        button.className = 'velor-image-picker-item';
        button.classList.toggle('is-selected', this.selectedImage?.id === image.id);
        button.disabled = this.context === 'rich-text' && ! hasFormats;
        button.title = button.disabled ? this.labels.no_formats : image.name;
        button.addEventListener('click', () => this.selectImage(image));
        preview.src = image.preview_url;
        preview.alt = '';
        name.textContent = image.name;
        filename.textContent = image.filename;
        button.append(preview, name, filename);

        return button;
    }

    private selectImage(image: ImagePickerImage): void {
        this.selectedImage = image;
        this.renderImages(this.filteredImages());
        this.selectButton.disabled = false;

        if (this.context !== 'rich-text') {
            return;
        }

        this.formatSelect.replaceChildren(...Object.keys(image.formats).map(format => {
            const option = document.createElement('option');
            option.value = format;
            option.textContent = format;

            return option;
        }));
        this.formatGroup.classList.remove('d-none');
        this.altGroup.classList.remove('d-none');
    }

    private filteredImages(): ImagePickerImage[] {
        const search = this.requiredElement<HTMLInputElement>('.velor-image-picker-search').value;
        const normalizedSearch = search.trim().toLocaleLowerCase();

        return normalizedSearch === ''
            ? this.images
            : this.images.filter(image => (
                [image.name, image.filename].join(' ').toLocaleLowerCase().includes(normalizedSearch)
            ));
    }

    private confirm(): void {
        if (this.selectedImage === null) {
            return;
        }

        if (this.context === 'image-upload') {
            this.complete({
                image: this.selectedImage,
                alt: '',
                format: null,
                url: this.selectedImage.preview_url,
            });

            return;
        }

        const format = this.formatSelect.value;
        const url = this.selectedImage.formats[format];

        if (format === '' || url === undefined) {
            return;
        }

        this.complete({
            image: this.selectedImage,
            alt: this.altInput.value.trim(),
            format,
            url,
        });
    }

    private complete(selection: ImagePickerSelection): void {
        if (this.accept !== undefined && ! this.accept(selection)) {
            return;
        }

        this.close(selection);
    }

    private close(selection: ImagePickerSelection | null): void {
        this.modal.remove();
        document.body.classList.remove('modal-open');
        this.previouslyFocused?.focus();
        this.resolveSelection?.(selection);
        this.resolveSelection = null;
    }

    private requiredElement<T extends Element = HTMLElement>(selector: string): T {
        return this.requiredElementFrom<T>(this.modal, selector);
    }

    private requiredElementFrom<T extends Element = HTMLElement>(parent: ParentNode, selector: string): T {
        const element = parent.querySelector<T>(selector);

        if (element === null) {
            throw new Error('Missing image picker element [' + selector + '].');
        }

        return element;
    }
}
