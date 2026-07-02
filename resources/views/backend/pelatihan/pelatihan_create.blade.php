@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Pelatihan /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('pelatihanadmin.store') }}" method="POST">
            @csrf
            <div class="row g-3">

                {{-- Nama Pelatihan --}}
                <div class="col-md-12">
                    <label class="form-label" for="nama_pelatihan">Nama Pelatihan</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="icon-base bx bx-captions"></i></span>
                        <input type="text" class="form-control" name="nama_pelatihan" placeholder="Nama Pelatihan" />
                    </div>
                </div>

                {{-- Kode Pelatihan (Master) --}}
                <div class="col-md-3">
                    <label class="form-label">Kode Pelatihan (Master)</label>
                    <select class="form-control" name="kode_pelatihan" required>
                        <option value="">-- Pilih Kode Pelatihan --</option>
                        @foreach ($MasterPelatihan as $row)
                            <option value="{{ $row->kode }}">
                                {{ $row->kode }} - {{ $row->keterangan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori --}}
                <div class="col-md-6">
                    <label class="form-label">Tagline Pelatihan</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="icon-base bx bx-list-ul"></i></span>
                        <input type="text" name="kategori" class="form-control"
                            placeholder="Tagline Pelatihan (contoh: Sertifikasi, Skill, Management) isi sebanyak mungkin">
                    </div>
                </div>

                {{-- Level --}}
                <div class="col-md-3">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-control">
                        <option value="">-- Pilih Level --</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                        <option value="Expert">Expert</option>
                    </select>
                </div>


                {{-- Deskripsi Singkat --}}
                <div class="col-md-12">
                    <label class="form-label" for="deskripsi_singkat">Deskripsi Singkat</label>
                    <textarea class="form-control" id="deskripsi_singkat" name="deskripsi_singkat" rows="3"></textarea>
                </div>

                {{-- Deskripsi Lengkap --}}
                <div class="col-md-12">
                    <label class="form-label" for="deskripsi">Deskripsi Lengkap</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="10"></textarea>
                </div>

                {{-- Photo Thumbnail --}}
                <div class="col-md-12">
                    <label class="form-label" for="photo">Thumbnail</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="thumbnail">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;"></div>
                </div>

                {{-- Durasi --}}
                <div class="col-md-4">
                    <label class="form-label">Durasi (Jam)</label>
                    <input type="number" class="form-control" name="durasi" placeholder="Contoh: 40">
                </div>

                {{-- Jumlah Sesi --}}
                <div class="col-md-4">
                    <label class="form-label">Jumlah Sesi</label>
                    <input type="number" class="form-control" name="jumlah_sesi" placeholder="Contoh: 10">
                </div>

                {{-- Harga --}}
                <div class="col-md-4">
                    <label class="form-label">Harga</label>
                    <input type="number" class="form-control" name="harga" placeholder="Contoh: 250000">
                </div>

                {{-- Gratis / Berbayar --}}
                <div class="col-md-4">
                    <label class="form-label">Gratis?</label>
                    <select class="form-control" name="is_free">
                        <option value="0">Berbayar</option>
                        <option value="1">Gratis</option>
                    </select>
                </div>

                {{-- Kapasitas --}}
                <div class="col-md-4">
                    <label class="form-label">Kapasitas Peserta</label>
                    <input type="number" class="form-control" name="kapasitas">
                </div>

                {{-- Tanggal --}}
                <div class="col-md-6">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="tanggal_mulai">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" name="tanggal_selesai">
                </div>

                {{-- Lokasi --}}
                <div class="col-md-12">
                    <label class="form-label">Lokasi</label>
                    <input type="text" class="form-control" name="lokasi" placeholder="Lokasi Pelatihan">
                </div>

                {{-- Status --}}
                <div class="col-md-12">
                    <label class="form-label">Status</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" checked value="draft">
                        <label class="form-check-label">Draft</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="publish">
                        <label class="form-check-label">Publish</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="nonaktif">
                        <label class="form-check-label">Nonaktif</label>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                    <button type="reset" class="btn btn-label-secondary">Reset</button>
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
