<?php

declare(strict_types=1);

namespace Velor\Images\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageProcessingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array'],
            'ids.*' => ['required', 'uuid', 'distinct'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function imageIds(): array
    {
        $imageIds = $this->validated('ids');

        if (! is_array($imageIds)) {
            return [];
        }

        return array_values(array_filter(
            $imageIds,
            static fn (mixed $imageId): bool => is_string($imageId),
        ));
    }
}
