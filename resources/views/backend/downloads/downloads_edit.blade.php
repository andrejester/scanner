@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Downloads /</span> Edit
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
        <form class="card-body" action="{{ route('downloads.update', $downloads->id) }}" method="post">
            @csrf
            @method('put')

            <p class="ms-3">You can check complete list of box icons from <a href="https://boxicons.com/"
                    target="_blank">https://boxicons.com</a></p>
            <div class="row g-6">

                <div class="col-md-12">
                    <label class="form-label" for="title">Downloads</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class='bx bx-book-reader'></i></span>
                        <input type="text" id="title" class="form-control" name="title"
                            placeholder="Judul Downloads" value="{{ $downloads->title }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="kategori">Kategori</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class='bx bx-user-voice'></i></span>
                        <select id="kategori" class="form-control" name="id_kategori">
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategori as $value)
                                <option value="{{ $value->id }}"
                                    {{ $downloads->id_kategori == $value->id ? 'selected' : '' }}>
                                    {{ $value->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="file">File</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder"
                                class="btn btn-outline-secondary">
                                <i class="tf-icons bx bx-photo-album"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="file"
                            value="{{ $downloads->file }}">
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
        $('#lfm').filemanager('image');
        $('#multiple-select-field').select2({
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false,
        });
    </script>
@endpush
