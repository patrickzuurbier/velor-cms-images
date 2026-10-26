export type ImagePickerImage = {
    id: string;
    name: string;
    filename: string;
    preview_url: string;
};

export type ImagePickerLabels = {
    close: string;
    empty: string;
    open: string;
    search: string;
    title: string;
};

export type ImagePickerResponse = {
    images: ImagePickerImage[];
    labels: ImagePickerLabels;
};

export type ImagePickerSelection = {
    image: ImagePickerImage;
    url: string;
};
