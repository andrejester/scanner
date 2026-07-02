@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Event /</span> Edit
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
        <form class="card-body" action="{{ route('masterevent.update', $masterevent->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="judul">Judul Event</label>
                    <input type="text" id="judul" class="form-control" name="judul"
                        value="{{ old('judul', $masterevent->judul) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="kategori">Kategori</label>
                    <input type="text" id="kategori" class="form-control" name="kategori"
                        value="{{ old('kategori', $masterevent->kategori) }}" placeholder="Seminar, Workshop, Wisuda, dll">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" class="form-control" name="tanggal_mulai"
                        value="{{ old('tanggal_mulai', optional($masterevent->tanggal_mulai)->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="tanggal_selesai">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" class="form-control" name="tanggal_selesai"
                        value="{{ old('tanggal_selesai', optional($masterevent->tanggal_selesai)->format('Y-m-d')) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="lokasi">Lokasi</label>
                    <input type="text" id="lokasi" class="form-control" name="lokasi"
                        value="{{ old('lokasi', $masterevent->lokasi) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="penyelenggara">Penyelenggara</label>
                    <input type="text" id="penyelenggara" class="form-control" name="penyelenggara"
                        value="{{ old('penyelenggara', $masterevent->penyelenggara) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="deskripsi">Deskripsi Singkat</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $masterevent->deskripsi) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="konten">Konten</label>
                    <textarea class="form-control" id="konten" name="konten" rows="10">{{ old('konten', $masterevent->konten) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="foto">Foto / Banner Event</label>
                    @if ($masterevent->foto)
                        <div class="mb-2">
                            <img src="{{ asset('storage/files/2/' . $masterevent->foto) }}" width="150" class="rounded">
                        </div>
                    @endif
                    <input type="file" id="foto" class="form-control" name="foto" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" id="status">
                        <option value="active" {{ $masterevent->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $masterevent->status == 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masterevent.index') }}" class="btn btn-label-secondary">Batal</a>
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

        CKEDITOR.replace('deskripsi', {
            toolbar: [{
                    name: 'document',
                    items: ['Source', 'Print']
                },
                {
                    name: 'styles',
                    items: ['Format', 'Font', 'FontSize']
                },
                {
                    name: 'align',
                    items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                },
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic', 'Underline']
                },
                {
                    name: 'paragraph',
                    items: ['NumberedList', 'BulletedList']
                }
            ],
            height: 150
        });

        CKEDITOR.replace('konten', {
            height: 300,
            // removeButtons: 'PasteFromWord',
            filebrowserBrowseUrl: "{{ route('ckeditor.files') }}",
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: "form"
        });
    </script>
@endpush
