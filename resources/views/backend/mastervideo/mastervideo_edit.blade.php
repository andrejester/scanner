@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Master Video /</span> Edit
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
        <form class="card-body" action="{{ route('mastervideo.update', $video->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="title">Judul Video <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $video->title) }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="id_kategori">Kategori</label>
                    <select name="id_kategori" id="id_kategori" class="form-control">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('id_kategori', $video->id_kategori) == $category->id ? 'selected' : '' }}>
                                {{ $category->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="tanggal">Tanggal</label>
                    <input type="date" id="tanggal" class="form-control" name="tanggal"
                        value="{{ old('tanggal', optional($video->tanggal)->format('Y-m-d')) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="active" {{ old('status', $video->status) == 'active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="inactive" {{ old('status', $video->status) == 'inactive' ? 'selected' : '' }}>
                            Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="source_type">Tipe Sumber Video <span class="text-danger">*</span></label>
                    <select name="source_type" id="source_type" class="form-control" required>
                        <option value="banner" {{ old('source_type', $video->source_type) == 'banner' ? 'selected' : '' }}>
                            Banner Utama</option>
                        <option value="youtube"
                            {{ old('source_type', $video->source_type) == 'youtube' ? 'selected' : '' }}>YouTube</option>
                        <option value="instagram"
                            {{ old('source_type', $video->source_type) == 'instagram' ? 'selected' : '' }}>Instagram
                        </option>
                        <option value="tiktok" {{ old('source_type', $video->source_type) == 'tiktok' ? 'selected' : '' }}>
                            TikTok</option>
                    </select>
                </div>

                <div class="col-md-6" id="link_field_group">
                    <label class="form-label" for="youtube">Link Video</label>
                    <input type="text" id="youtube" class="form-control" name="youtube"
                        value="{{ old('youtube', $video->youtube) }}" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                @if ($video->video)
                    <div class="col-md-12">
                        <label class="form-label">Preview Video Saat Ini</label>
                        <div class="mb-2">
                            @php
                                $videoUrl = $video->video;
                                if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                                    $videoUrl = asset('storage/files/2/' . $video->video);
                                }
                            @endphp
                            <video width="320" controls class="rounded">
                                <source src="{{ $videoUrl }}" type="video/mp4">
                                Browser Anda tidak mendukung video tag.
                            </video>
                        </div>
                    </div>
                @endif

                <div class="col-md-12 d-none" id="video_field_group">
                    <label class="form-label" for="video">Upload File Video Baru</label>
                    <input type="file" id="video" class="form-control" name="video" accept="video/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti file. Gunakan file MP4, MOV, AVI, MKV,
                        WEBM. Max 100MB.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6">{{ old('deskripsi', $video->deskripsi) }}</textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('mastervideo.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        function updateVideoFields() {
            const sourceType = document.getElementById('source_type').value;
            const linkGroup = document.getElementById('link_field_group');
            const videoGroup = document.getElementById('video_field_group');
            const linkInput = document.getElementById('youtube');
            const videoInput = document.getElementById('video');

            if (sourceType === 'banner') {
                linkGroup.classList.add('d-none');
                videoGroup.classList.remove('d-none');
                linkInput.removeAttribute('required');
                videoInput.setAttribute('required', 'required');
            } else {
                linkGroup.classList.remove('d-none');
                videoGroup.classList.add('d-none');
                linkInput.setAttribute('required', 'required');
                videoInput.removeAttribute('required');
            }
        }

        document.getElementById('source_type').addEventListener('change', updateVideoFields);
        document.addEventListener('DOMContentLoaded', updateVideoFields);
    </script>
@endsection
