@extends('app')

@section('content')
    <div
        class="velor-image-library-upload"
        data-progress-label="{{ __('velor-images::resources.images.upload.progress') }}"
        data-complete-label="{{ __('velor-images::resources.images.upload.complete') }}"
        data-failed-label="{{ __('velor-images::resources.images.upload.failed') }}"
        data-generic-error="{{ __('velor-images::resources.images.upload.generic_error') }}"
        data-status-waiting="{{ __('velor-images::resources.images.upload.status_waiting') }}"
        data-status-uploading="{{ __('velor-images::resources.images.upload.status_uploading') }}"
        data-status-uploaded="{{ __('velor-images::resources.images.upload.status_uploaded') }}"
        data-status-failed="{{ __('velor-images::resources.images.upload.status_failed') }}"
    >
        <x-cms.form.form :resource="$resource"/>

        <section class="velor-image-upload-progress card d-none" aria-live="polite">
            <div class="card-body">
                <h4>{{ __('velor-images::resources.images.upload.title') }}</h4>
                <div class="velor-image-upload-label mb-1"></div>
                <div class="progress mb-3" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="progress-bar velor-image-upload-bar" style="width: 0%"></div>
                </div>
                <div class="velor-image-upload-summary fw-semibold mb-2"></div>
                <ul class="velor-image-upload-files list-group"></ul>
            </div>
        </section>
    </div>
@endsection
