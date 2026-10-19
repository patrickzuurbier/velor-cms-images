export type ProcessingStatus = 'pending' | 'processing' | 'completed' | 'failed';

type ProcessingResponse = {
    images: Array<{
        id: string;
        processing_status: ProcessingStatus;
    }>;
};

const processingChunkSize = 50;
const processingPollInterval = 1000;

export async function pollImageProcessingStatuses(
    imageIds: string[],
    processingUrl: string,
    csrfToken: string,
    onUpdate: (statuses: Map<string, ProcessingStatus>) => void,
): Promise<void> {
    if (! processingUrl || ! csrfToken) {
        throw new Error('Image processing status polling is not configured.');
    }

    let unresolvedImageIds = imageIds;

    while (unresolvedImageIds.length > 0) {
        const statuses = await fetchProcessingStatuses(unresolvedImageIds, processingUrl, csrfToken);

        onUpdate(statuses);
        unresolvedImageIds = unresolvedImageIds.filter(imageId => {
            const status = statuses.get(imageId);

            if (! status) {
                throw new Error('An image processing status is missing.');
            }

            return status === 'pending' || status === 'processing';
        });

        if (unresolvedImageIds.length > 0) {
            await delay(processingPollInterval);
        }
    }
}

async function fetchProcessingStatuses(
    imageIds: string[],
    processingUrl: string,
    csrfToken: string,
): Promise<Map<string, ProcessingStatus>> {
    const statuses = new Map<string, ProcessingStatus>();

    for (const imageIdChunk of chunks(imageIds, processingChunkSize)) {
        const body = new FormData();

        body.append('_token', csrfToken);
        imageIdChunk.forEach(imageId => body.append('ids[]', imageId));

        const response = await fetch(processingUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        });
        const data = parseProcessingResponse(await response.text());

        if (! response.ok || ! data) {
            throw new Error('The image processing status response is invalid.');
        }

        data.images.forEach(image => statuses.set(image.id, image.processing_status));
    }

    return statuses;
}

function parseProcessingResponse(responseText: string): ProcessingResponse | null {
    try {
        const response = JSON.parse(responseText) as unknown;

        if (! response || typeof response !== 'object' || ! ('images' in response) || ! Array.isArray(response.images)) {
            return null;
        }

        if (! response.images.every(image => isProcessingImage(image))) {
            return null;
        }

        return {images: response.images};
    } catch {
        return null;
    }
}

function isProcessingImage(image: unknown): image is ProcessingResponse['images'][number] {
    return Boolean(image && typeof image === 'object' && 'id' in image && typeof image.id === 'string'
        && 'processing_status' in image && isProcessingStatus(image.processing_status));
}

export function isProcessingStatus(status: unknown): status is ProcessingStatus {
    return status === 'pending' || status === 'processing' || status === 'completed' || status === 'failed';
}

function chunks<T>(values: T[], chunkSize: number): T[][] {
    const valueChunks: T[][] = [];

    for (let index = 0; index < values.length; index += chunkSize) {
        valueChunks.push(values.slice(index, index + chunkSize));
    }

    return valueChunks;
}

function delay(milliseconds: number): Promise<void> {
    return new Promise(resolve => window.setTimeout(resolve, milliseconds));
}
