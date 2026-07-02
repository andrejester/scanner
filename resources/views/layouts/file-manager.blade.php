@extends('layouts.app')
@section('content')
    @can('filemanager_write')
        <div class="container-fluid">
            <iframe src="/laravel-filemanager" style="width: 100%; height: 700px; overflow: hidden; border: 1px;"></iframe>
        </div>
    @endcan
@endsection
