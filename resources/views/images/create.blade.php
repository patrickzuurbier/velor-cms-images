@extends('app')

@section('content')
    <div class="velor-image-library-upload">
        <x-cms.form.form :resource="$resource"/>
    </div>
@endsection
