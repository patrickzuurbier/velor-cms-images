import { openImagePicker, preloadImagePicker } from './imagePicker';

type ExistingImageSelection = {
    id: string;
    name: string;
    alt: string;
    url: string;
};

type ImageUploadReadyDetail = {
    select: (image: ExistingImageSelection) => boolean;
};

const initializedDropzones = new WeakSet<HTMLElement>();

export function initializeImageUploadPicker(): void {
    document.addEventListener('velor:image-upload-ready', event => {
        if (! (event instanceof CustomEvent) || ! (event.target instanceof HTMLElement)) {
            return;
        }

        void attachPickerButton(event.target, event.detail as ImageUploadReadyDetail);
    });
}

async function attachPickerButton(dropzone: HTMLElement, detail: ImageUploadReadyDetail): Promise<void> {
    if (initializedDropzones.has(dropzone) || typeof detail.select !== 'function') {
        return;
    }

    initializedDropzones.add(dropzone);

    try {
        const data = await preloadImagePicker();
        const input = dropzone.querySelector('.image-upload-input');

        if (! (input instanceof HTMLInputElement)) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-primary mt-3 velor-image-picker-open';
        button.innerHTML = '<i class="bi bi-images me-1"></i>';
        button.append(data.labels.open);
        button.addEventListener('click', async () => {
            await openImagePicker('image-upload', selection => detail.select({
                id: selection.image.id,
                name: selection.image.name,
                alt: selection.alt,
                url: selection.url,
            })).catch(() => null);
        });
        input.insertAdjacentElement('afterend', button);
    } catch {
        initializedDropzones.delete(dropzone);
    }
}
