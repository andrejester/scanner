@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Download /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('masterdownload.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="id_kategori">Kategori Download</label>
                    <select id="id_kategori" class="form-control" name="id_kategori">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('id_kategori') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="title">Judul Download</label>
                    <input type="text" id="title" class="form-control" name="title" value="{{ old('title') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="title_seo">Judul SEO</label>
                    <input type="text" id="title_seo" class="form-control" name="title_seo"
                        value="{{ old('title_seo') }}" placeholder="judul-download-atau-judul-seo">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="file">File PDF <small class="text-muted">(maks 10MB)</small></label>
                    <input type="file" id="file" class="form-control" name="file" accept=".pdf">
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masterdownload.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection
