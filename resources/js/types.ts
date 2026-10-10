export type ImagePickerImage = {
    id: string;
    name: string;
    filename: string;
    preview_url: string;
    formats: Record<string, string>;
};

export type ImagePickerLabels = {
    alt: string;
    close: string;
    empty: string;
    format: string;
    no_formats: string;
    open: string;
    search: string;
    select: string;
    title: string;
};

export type ImagePickerResponse = {
    images: ImagePickerImage[];
    labels: ImagePickerLabels;
};

export type ImagePickerContext = 'image-upload' | 'rich-text';

export type ImagePickerSelection = {
    image: ImagePickerImage;
    alt: string;
    format: string | null;
    url: string;
};
