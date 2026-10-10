import { openImagePicker } from './imagePicker';

type EditorRange = {
    index: number;
};

type ToolbarModule = {
    addHandler: (name: string, handler: () => void) => void;
};

type RichTextEditor = {
    formatText: (index: number, length: number, formats: Record<string, string>, source: string) => void;
    getLength: () => number;
    getModule: (name: string) => unknown;
    getSelection: (focus?: boolean) => EditorRange | null;
    insertEmbed: (index: number, type: string, value: string, source: string) => void;
    setSelection: (index: number, length: number, source: string) => void;
    update: (source: string) => void;
};

type RichTextReadyDetail = {
    quill: RichTextEditor;
    synchronize: () => void;
};

const initializedEditors = new WeakSet<HTMLElement>();

export function initializeRichTextPicker(): void {
    document.addEventListener('velor:rich-text-ready', event => {
        if (! (event instanceof CustomEvent) || ! (event.target instanceof HTMLElement)) {
            return;
        }

        const detail = event.detail as RichTextReadyDetail;
        const toolbar = detail.quill.getModule('toolbar');

        if (initializedEditors.has(event.target) || ! isToolbarModule(toolbar)) {
            return;
        }

        initializedEditors.add(event.target);
        toolbar.addHandler('image', () => {
            void selectImage(detail);
        });
    });
}

async function selectImage(detail: RichTextReadyDetail): Promise<void> {
    const range = detail.quill.getSelection(true);
    const selection = await openImagePicker('rich-text').catch(() => null);

    if (selection === null || selection.format === null) {
        return;
    }

    const index = range?.index ?? detail.quill.getLength();

    detail.quill.insertEmbed(index, 'image', selection.url, 'user');
    detail.quill.formatText(index, 1, {
        alt: selection.alt,
        'data-image-id': selection.image.id,
        'data-format': selection.format,
    }, 'user');
    detail.quill.update('user');
    detail.synchronize();
    detail.quill.setSelection(index + 1, 0, 'user');
}

function isToolbarModule(value: unknown): value is ToolbarModule {
    return typeof value === 'object'
        && value !== null
        && 'addHandler' in value
        && typeof value.addHandler === 'function';
}
