@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Layanan /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('masterlayanan.store') }}" method="post">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" id="title" class="form-control" name="title" placeholder="Judul">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="icon">Icon</label>
                    <div class="input-group input-group-merge">
                        <input type="text" id="basic-icon-default" class="form-control"
                            name="icon"aria-describedby="basic-icon-default2" placeholder="Masukkan Icon">
                        <span id="basic-icon-default2" class="input-group-text">ex: fa-home</span>
                    </div>
                    <div id="floatingInputHelp" class="form-text">Icon Bisa dilihat disini <a
                            href="https://fontawesome.com/v4/icons/" target="_blank">daftar icon</a> </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="keterangan">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="10" placeholder="Keterangan Layanan"></textarea>
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
        var editorElements = ["#keterangan1"];
        // Inisialisasi RichTextEditor untuk setiap elemen
        editorElements.forEach(function(selector) {
            new RichTextEditor(selector);
        });
    </script>
@endpush
