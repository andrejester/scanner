@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Banner Utama /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('banner.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="title">Judul Banner <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title" value="{{ old('title') }}"
                        required>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">File Banner (Foto / Video) <span
                            class="text-danger">*</span></label>
                    <input type="file" id="photo" class="form-control" name="photo" accept="image/*,video/*">
                    <small class="text-muted">Rekomendasi ukuran: 1920x800px untuk foto. Format: JPG, JPEG, PNG, WEBP, MP4,
                        MOV, AVI, MKV. Max: 20MB</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="active" value="active"
                                checked>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive">
                            <label class="form-check-label" for="inactive">Inactive</label>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('banner.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection

@push('custom_js')
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
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
