@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Alumni /</span> Edit
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
        <form class="card-body" action="{{ route('masteralumni.update', $masteralumni->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-8">
                    <label class="form-label" for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" class="form-control" name="nama"
                        value="{{ old('nama', $masteralumni->nama) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="nim">NIM</label>
                    <input type="text" id="nim" class="form-control" name="nim"
                        value="{{ old('nim', $masteralumni->nim) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="jenjang">Jenjang</label>
                    <select id="jenjang" class="form-control" name="jenjang">
                        <option value="">-- Pilih --</option>
                        <option value="s2" {{ old('jenjang', $masteralumni->jenjang) == 's2' ? 'selected' : '' }}>S2
                        </option>
                        <option value="s3" {{ old('jenjang', $masteralumni->jenjang) == 's3' ? 'selected' : '' }}>S3
                        </option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label" for="program_studi">Program Studi</label>
                    <input type="text" id="program_studi" class="form-control" name="program_studi"
                        value="{{ old('program_studi', $masteralumni->program_studi) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="tahun_lulus">Tahun Lulus</label>
                    <input type="number" id="tahun_lulus" class="form-control" name="tahun_lulus"
                        value="{{ old('tahun_lulus', $masteralumni->tahun_lulus) }}" min="1900"
                        max="{{ date('Y') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="pekerjaan">Pekerjaan</label>
                    <input type="text" id="pekerjaan" class="form-control" name="pekerjaan"
                        value="{{ old('pekerjaan', $masteralumni->pekerjaan) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="instansi">Instansi / Perusahaan</label>
                    <input type="text" id="instansi" class="form-control" name="instansi"
                        value="{{ old('instansi', $masteralumni->instansi) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="testimoni">Testimoni</label>
                    <textarea class="form-control" id="testimoni" name="testimoni" rows="5">{{ old('testimoni', $masteralumni->testimoni) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="foto">Foto</label>
                    @if ($masteralumni->foto)
                        <div class="mb-2">
                            <img src="{{ asset('storage/files/2/' . $masteralumni->foto) }}" width="80"
                                class="rounded-circle">
                        </div>
                    @endif
                    <input type="file" id="foto" class="form-control" name="foto" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. Format: JPG, PNG, WEBP (Max 2MB).
                        Foto akan otomatis diresize ke 390x400px.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control" id="is_active">
                        <option value="1" {{ $masteralumni->is_active == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $masteralumni->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1"
                            {{ old('is_pinned', $masteralumni->is_pinned) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_pinned">
                            <i class="bx bxs-star text-warning"></i> Tampilkan di Alumni Stories (Pinned)
                        </label>
                        <small class="d-block text-muted">Alumni yang di-pin akan ditampilkan di bagian Alumni Stories pada
                            halaman utama</small>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masteralumni.index') }}" class="btn btn-label-secondary">Batal</a>
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

        CKEDITOR.replace('testimoni', {
            height: 300,
            // removeButtons: 'PasteFromWord',
            filebrowserBrowseUrl: "{{ route('ckeditor.files') }}",
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: "form"
        });
    </script>
@endpush
