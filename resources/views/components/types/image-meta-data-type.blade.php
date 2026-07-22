@if(count($metadata) > 0)
    <dl class="image-format-meta image-meta-data-list">
        @foreach($metadata as $label => $value)
            <div>
                <dt>{{ $label }}</dt>
                <dd>{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
@endif
