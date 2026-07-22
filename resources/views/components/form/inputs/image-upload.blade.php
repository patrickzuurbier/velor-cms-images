<div class="mb-3">
    <label for="{{ $inputData->getName() }}" class="form-label">
        {{ $inputData->getLabel() }}@if($inputData->isRequired())<span class="text-danger">*</span>@endif
    </label>
    @include('components.form.inputs.partials.help')
    <div class="image-upload border rounded p-4 text-center @error($inputData->getAttribute()) border-danger @enderror">
        <input
            type="file"
            class="form-control image-upload-input @error($inputData->getAttribute()) is-invalid @enderror"
            id="{{ $inputData->getName() }}"
            name="{{ $inputData->getName() }}"
            accept="image/*"
        >
        <div class="image-upload-hint mt-3">
            Drop an image here or browse your local disk.
        </div>
        <div class="image-upload-file fw-bold mt-2"></div>
    </div>
    @error($inputData->getAttribute())
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
