@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Facilities /</span> Tambah
    </h4>
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading mb-1">Error</h6>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    @endif
    <div class="card mb-4">
        <form id="form1" class="card-body" action="{{ route('facilities.store') }}" method="post">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="title">Name</label>
                    <input type="text" id="name" class="form-control" name="name">
                </div>

                <div class="col mb-12">
                    <label class="form-check-label">Category</label>
                    <select name="category" class="form-control" data-placeholder="Choose anything">
                        <option value="ARS Property">ARS Property</option>
                        <option value="Ashraya House">Ashraya House</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>

                <div class="col mb-12">
                    <label class="form-check-label">Status</label>
                    <div class="col mt-2">
                        <select name="status" class="form-control" id="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>

        </form>
    </div>
@endsection

@push('custom_js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        $('#lfm').filemanager('image');

        CKEDITOR.on('instanceReady', function(evt) {
            var editor = evt.editor;

            editor.on('notificationShow', function(event) {
                var notification = event.data.notification;

                setTimeout(function() {
                    if (notification && notification.hide) {
                        notification.hide();
                    }
                }, 100);
            });
        });

        CKEDITOR.replace('description', {
            height: 300,
            // removeButtons: 'PasteFromWord',
            filebrowserBrowseUrl: "{{ route('ckeditor.files') }}",
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: "form"
        });
    </script>
@endpush
