@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Banner Utama /</span> Edit
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
        <form class="card-body" action="{{ route('banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="title">Judul Banner <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $banner->title) }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $banner->description) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">File Banner (Foto / Video) <span
                            class="text-danger">*</span></label>
                    @if ($banner->photo)
                        <div class="mb-2">
                            @php
                                $mediaUrl = $banner->photo;
                                $isVideo = false;

                                if (!filter_var($mediaUrl, FILTER_VALIDATE_URL)) {
                                    $mediaUrl = asset('storage/files/2/' . $mediaUrl);
                                }

                                $extension = pathinfo($banner->photo, PATHINFO_EXTENSION);
                                $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
                                if (in_array(strtolower($extension), $videoExtensions, true)) {
                                    $isVideo = true;
                                }
                            @endphp

                            @if ($isVideo)
                                <video width="300" controls class="rounded">
                                    <source src="{{ $mediaUrl }}" type="video/{{ strtolower($extension) }}">
                                    Your browser does not support the video tag.
                                </video>
                            @else
                                <img src="{{ $mediaUrl }}" width="150" class="rounded">
                            @endif
                        </div>
                    @endif
                    <input type="file" id="photo" class="form-control" name="photo" accept="image/*,video/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti file. Rekomendasi ukuran: 1920x800px
                        untuk foto.
                        Format: JPG, JPEG, PNG, WEBP, MP4, MOV, AVI, MKV. Max: 20MB</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control" id="status">
                        <option value="active" {{ $banner->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $banner->status == 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
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
