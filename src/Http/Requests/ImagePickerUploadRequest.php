<?php

declare(strict_types=1);

namespace Velor\Images\Http\Requests;

use App\Models\Image;
use Illuminate\Foundation\Http\FormRequest;

class ImagePickerUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Image::class) === true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image'],
        ];
    }
}
