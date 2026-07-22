@if(count($formats) > 0)
    <div class="image-format-grid">
        @foreach($formats as $format)
            <div class="image-format-card">
                <div class="image-format-preview-wrap">
                    <img
                        src="{{ $format['url'] }}"
                        alt=""
                        class="image-format-preview"
                    >

                    <button
                        type="button"
                        class="image-format-copy-url"
                        data-copy-url="{{ $format['url'] }}"
                        data-copy-label="{{ __('cms.actions.copy_url') }}"
                        data-copied-label="{{ __('cms.actions.copied') }}"
                        title="{{ __('cms.actions.copy_url') }}"
                        aria-label="{{ __('cms.actions.copy_url') }}"
                    >
                        <i class="bi bi-link-45deg"></i>
                    </button>
                </div>

                <dl class="image-format-meta">
                    <div>
                        <dt>{{ $format['label'] }}</dt>
                        <dd>{{ $format['width'] }} x {{ $format['height'] }} px</dd>
                    </div>

                    <div>
                        <dt>{{ __('velor-images::resources.images.metadata.mime_type') }}</dt>
                        <dd>{{ $format['mime_type'] }}</dd>
                    </div>

                    <div>
                        <dt>{{ __('velor-images::resources.images.metadata.extension') }}</dt>
                        <dd>{{ $format['extension'] }}</dd>
                    </div>

                    <div>
                        <dt>{{ __('velor-images::resources.images.metadata.size') }}</dt>
                        <dd>{{ $format['size_label'] }}</dd>
                    </div>
                </dl>
            </div>
        @endforeach
    </div>
@endif
