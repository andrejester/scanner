@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Downloads /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('downloads.store') }}" method="POST">
            @csrf

            <p class="ms-3">You can check complete list of box icons from <a href="https://boxicons.com/"
                    target="_blank">https://boxicons.com</a></p>
            <div class="row g-6">

                <div class="col-md-12">
                    <label class="form-label" for="title">Judul Downloads</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class='bx bx-book-reader'></i></span>
                        <input type="text" id="title" class="form-control" name="title"
                            placeholder="Judul Downloads">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="kategori">Kategori</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class='bx bx-user-voice'></i></span>
                        <select id="kategori" class="form-control" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategori as $value)
                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="downloads">File</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="file">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;"></div>
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
    <script>
        // Mengubah pengaturan lfm agar bisa memilih file selain gambar
        $('#lfm').filemanager('file'); // Sebelumnya 'image', ubah jadi 'file'

        $('#multiple-select-field').select2({
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false,
        });
    </script>
@endpush
