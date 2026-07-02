@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">System /</span> Sambutan Direksi
    </h4>

    <div class="card mb-4">
        <form class="card-body" id="form1">
            <h6>Sambutan Direktur</h6>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="direktur">Nama Direksi</label>
                    <input type="text" id="direktur" class="form-control" name="direktur"
                        value="{{ $sambutan->direktur ?? 'Nama Direktur' }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="sambutan">Sambutan</label>
                    <textarea class="form-control" id="sambutan" name="sambutan" rows="10">{{ $sambutan->sambutan ?? 'Kata Sambutan' }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="photo"
                            value="{{ $sambutan->photo ?? 'Masukkan Photo' }}">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if (!empty($sambutan->photo))
                            <img src="{{ asset($sambutan->photo) }}" style="max-height: 100px;" />
                        @endif
                    </div>
                    @error('photo')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="button" onclick="save('{{ route('sambutan.update', $sambutan->id) }}','put')"
                        class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>
        </form>
    </div>
@endsection

@push('custom_js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('image');

        var editorElements = ["#sambutan"];

        // Inisialisasi RichTextEditor untuk setiap elemen
        editorElements.forEach(function(selector) {
            new RichTextEditor(selector);
        });
    </script>
@endpush
