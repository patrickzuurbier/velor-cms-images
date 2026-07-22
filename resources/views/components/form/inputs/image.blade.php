@if(is_string($inputData->getValue()) && $inputData->getValue() !== '')
    <div class="mb-3">
        <label class="form-label">{{ $inputData->getLabel() }}</label>
        @include('components.form.inputs.partials.help')
        <img
            src="{{ $inputData->getValue() }}"
            alt=""
            class="cms-image-preview"
        >
    </div>
@endif
