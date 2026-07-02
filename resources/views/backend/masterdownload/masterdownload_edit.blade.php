@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Download /</span> Edit
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
        <form class="card-body" action="{{ route('masterdownload.update', $masterdownload->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="id_kategori">Kategori Download</label>
                    <select id="id_kategori" class="form-control" name="id_kategori">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}"
                                {{ old('id_kategori', $masterdownload->id_kategori) == $kat->id ? 'selected' : '' }}>
                                {{ $kat->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="title">Judul Download</label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $masterdownload->title) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="title_seo">Judul SEO</label>
                    <input type="text" id="title_seo" class="form-control" name="title_seo"
                        value="{{ old('title_seo', $masterdownload->title_seo) }}"
                        placeholder="judul-download-atau-judul-seo">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="file">File PDF <small class="text-muted">(maks 10MB)</small></label>
                    @if ($masterdownload->file)
                        <div class="mb-2">
                            <a href="{{ asset('storage/files/2/' . $masterdownload->file) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-file-pdf"></i> Lihat File Saat Ini
                            </a>
                        </div>
                    @endif
                    <input type="file" id="file" class="form-control" name="file" accept=".pdf">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masterdownload.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection
