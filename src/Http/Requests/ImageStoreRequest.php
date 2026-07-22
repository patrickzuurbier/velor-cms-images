<?php

declare(strict_types=1);

namespace Velor\Images\Http\Requests;

use App\Contracts\Factories\Validation\ResourceValidationAttributesFactoryInterface;
use App\Contracts\Factories\Validation\ResourceValidationRulesFactoryInterface;
use App\Http\Requests\AbstractFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Velor\Images\Models\Image;

class ImageStoreRequest extends AbstractFormRequest
{
    public function __construct(
        protected ResourceValidationRulesFactoryInterface $rulesFactory,
        protected ResourceValidationAttributesFactoryInterface $attributesFactory,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->rulesFactory->make(Image::class);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->attributesFactory->make(Image::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function getRules(): array
    {
        return $this->rules();
    }
}
