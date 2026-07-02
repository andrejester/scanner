@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Pelatihan /</span> Edit
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
        <form class="card-body" action="{{ route('pelatihanadmin.update', $pelatihanadmin->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- Nama Pelatihan --}}
                <div class="col-md-12">
                    <label class="form-label">Nama Pelatihan</label>
                    <input type="text" class="form-control" name="nama_pelatihan"
                        value="{{ old('nama_pelatihan', $pelatihanadmin->nama_pelatihan) }}" placeholder="Nama Pelatihan">
                </div>

                {{-- Kode Pelatihan --}}
                <div class="col mb-3">
                    <label class="form-label">Kode Pelatihan</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-list-ul"></i></span>
                        <select name="kode_pelatihan" class="form-control">
                            <option value="">-- Pilih Kode Pelatihan --</option>
                            @foreach ($categoryAll as $value)
                                <option value="{{ $value->kode }}"
                                    {{ $value->kode == old('kode_pelatihan', $pelatihanadmin->kode_pelatihan) ? 'selected' : '' }}>
                                    {{ $value->keterangan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Kategori --}}
                <div class="col-md-6">
                    <label class="form-label">Tagline Pelatihan</label>
                    <input type="text" class="form-control" name="kategori"
                        value="{{ old('kategori', $pelatihanadmin->kategori) }}" placeholder="Tagline Pelatihan">
                </div>

                {{-- Level --}}
                <div class="col-md-3">
                    <label class="form-label">Level</label>
                    <select class="form-control" name="level">
                        <option value="">-- Pilih Level --</option>
                        <option value="Beginner" {{ $pelatihanadmin->level == 'Beginner' ? 'selected' : '' }}>Beginner
                        </option>
                        <option value="Intermediate" {{ $pelatihanadmin->level == 'Intermediate' ? 'selected' : '' }}>
                            Intermediate</option>
                        <option value="Expert" {{ $pelatihanadmin->level == 'Expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                </div>

                {{-- Kode Instruktur --}}
                {{-- <div class="col-md-6">
                    <label class="form-label">Kode Instruktur</label>
                    <input type="text" class="form-control" name="kode_instruktur"
                        value="{{ old('kode_instruktur', $pelatihanadmin->kode_instruktur) }}">
                </div> --}}

                {{-- Deskripsi Singkat --}}
                <div class="col-md-12">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea class="form-control" id="summary" name="deskripsi_singkat" rows="3">
                {{ old('deskripsi_singkat', $pelatihanadmin->deskripsi_singkat) }}
            </textarea>
                </div>

                {{-- Deskripsi --}}
                <div class="col-md-12">
                    <label class="form-label">Deskripsi Lengkap</label>
                    <textarea class="form-control" id="description" name="deskripsi" rows="10">
                {{ old('deskripsi', $pelatihanadmin->deskripsi) }}
            </textarea>
                </div>

                {{-- Thumbnail --}}
                <div class="col-md-12">
                    <label class="form-label">Thumbnail</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="thumbnail"
                            value="{{ old('thumbnail', $pelatihanadmin->thumbnail) }}">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if ($pelatihanadmin->thumbnail)
                            <img src="{{ $pelatihanadmin->thumbnail }}" style="height: 100px;">
                        @endif
                    </div>
                </div>

                {{-- Durasi --}}
                <div class="col-md-4">
                    <label class="form-label">Durasi (Jam)</label>
                    <input type="number" class="form-control" name="durasi"
                        value="{{ old('durasi', $pelatihanadmin->durasi) }}">
                </div>

                {{-- Jumlah Sesi --}}
                <div class="col-md-4">
                    <label class="form-label">Jumlah Sesi</label>
                    <input type="number" class="form-control" name="jumlah_sesi"
                        value="{{ old('jumlah_sesi', $pelatihanadmin->jumlah_sesi) }}">
                </div>

                {{-- Kapasitas --}}
                <div class="col-md-4">
                    <label class="form-label">Kapasitas Peserta</label>
                    <input type="number" class="form-control" name="kapasitas"
                        value="{{ old('kapasitas', $pelatihanadmin->kapasitas) }}">
                </div>

                {{-- Harga --}}
                <div class="col-md-6">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" class="form-control" name="harga" step="0.01"
                        value="{{ old('harga', $pelatihanadmin->harga) }}">
                </div>

                {{-- Gratis / Berbayar --}}
                <div class="col-md-6">
                    <label class="form-label">Gratis?</label>
                    <select class="form-control" name="is_free">
                        <option value="0" {{ $pelatihanadmin->is_free == 0 ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ $pelatihanadmin->is_free == 1 ? 'selected' : '' }}>Ya</option>
                    </select>
                </div>

                {{-- Jadwal --}}
                <div class="col-md-6">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="tanggal_mulai"
                        value="{{ old('tanggal_mulai', $pelatihanadmin->tanggal_mulai) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" name="tanggal_selesai"
                        value="{{ old('tanggal_selesai', $pelatihanadmin->tanggal_selesai) }}">
                </div>

                {{-- Lokasi --}}
                <div class="col-md-12">
                    <label class="form-label">Lokasi</label>
                    <input type="text" class="form-control" name="lokasi"
                        value="{{ old('lokasi', $pelatihanadmin->lokasi) }}">
                </div>

                {{-- Status --}}
                <div class="col-md-12">
                    <label class="form-label">Status</label><br>

                    @foreach (['draft', 'publish', 'nonaktif'] as $status)
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="{{ $status }}"
                                {{ $pelatihanadmin->status == $status ? 'checked' : '' }}>
                            <span class="form-check-label text-capitalize">{{ $status }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Update</button>
                    <a href="{{ route('pelatihanadmin.index') }}" class="btn btn-label-secondary">Kembali</a>
                </div>

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
            height: 300
        });

        CKEDITOR.replace('deskripsi_singkat', {
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
    </script>
@endpush
