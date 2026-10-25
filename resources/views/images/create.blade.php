@extends('app')

@section('content')
    <div
        class="velor-image-library-upload"
        data-processing-url="{{ route('images.processing-statuses') }}"
        data-progress-label="{{ __('velor-images::resources.images.upload.progress') }}"
        data-generic-error="{{ __('velor-images::resources.images.upload.generic_error') }}"
        data-processing-progress-label="{{ __('velor-images::resources.images.upload.processing_progress') }}"
        data-processing-error="{{ __('velor-images::resources.images.upload.processing_error') }}"
        data-result-complete="{{ __('velor-images::resources.images.upload.result_complete') }}"
        data-result-partial="{{ __('velor-images::resources.images.upload.result_partial') }}"
        data-result-failed="{{ __('velor-images::resources.images.upload.result_failed') }}"
        data-status-waiting="{{ __('velor-images::resources.images.upload.status_waiting') }}"
        data-status-uploading="{{ __('velor-images::resources.images.upload.status_uploading') }}"
        data-status-pending="{{ __('velor-images::resources.images.upload.status_pending') }}"
        data-status-processing="{{ __('velor-images::resources.images.upload.status_processing') }}"
        data-status-completed="{{ __('velor-images::resources.images.upload.status_completed') }}"
        data-status-failed="{{ __('velor-images::resources.images.upload.status_failed') }}"
    >
        <x-cms.form.form :resource="$resource"/>

        <section class="velor-image-upload-progress card d-none" aria-live="polite">
            <div class="card-body">
                <h4>{{ __('velor-images::resources.images.upload.title') }}</h4>
                <div id="velor-image-upload-label" class="velor-image-upload-label mb-1"></div>
                <div class="progress mb-3" role="progressbar" aria-labelledby="velor-image-upload-label" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="progress-bar velor-image-upload-bar" style="width: 0%"></div>
                </div>
                <div id="velor-image-processing-label" class="velor-image-processing-label mb-1"></div>
                <div class="progress mb-3" role="progressbar" aria-labelledby="velor-image-processing-label" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="progress-bar bg-success velor-image-processing-bar" style="width: 0%"></div>
                </div>
                <div class="velor-image-upload-summary fw-semibold mb-2"></div>
                <ul class="velor-image-upload-files list-group"></ul>
                <div class="velor-image-upload-actions d-none mt-3">
                    <a class="btn btn-primary" href="{{ route('images.index', ['scope' => 'unused']) }}">
                        {{ __('velor-images::resources.images.upload.view_images') }}
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
