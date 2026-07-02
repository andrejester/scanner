@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Master Pelatihan /</span> Edit
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
        <form class="card-body" action="{{ route('masterpelatihan.update', $masterpelatihan->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="kode">Kode Pelatihan</label>
                    <input type="text" id="kode" class="form-control" name="kode"
                        value="{{ $masterpelatihan->kode }}" placeholder="Kode Pelatihan" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="keterangan">Kategori Pelatihan</label>
                    <input type="text" id="keterangan" class="form-control" name="keterangan"
                        value="{{ $masterpelatihan->keterangan }}" placeholder="Kategori Pelatihan">
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>

        </form>
    </div>
@endsection
@push('custom_js')
    <script>
        var editorElements = ["#deskripsi"];
        // Inisialisasi RichTextEditor untuk setiap elemen
        editorElements.forEach(function(selector) {
            new RichTextEditor(selector);
        });
    </script>
@endpush
