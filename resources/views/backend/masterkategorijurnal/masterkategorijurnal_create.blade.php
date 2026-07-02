@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Kategori Jurnal /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('masterkategorijurnal.store') }}" method="POST">
            @csrf
            <div class="row g-3">

                <div class="col-md-8">
                    <label class="form-label" for="nama">Nama Kategori</label>
                    <input type="text" id="nama" class="form-control" name="nama" value="{{ old('nama') }}"
                        placeholder="Contoh: Jurnal Manajemen S2">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="jenjang">Jenjang</label>
                    <select id="jenjang" class="form-control" name="jenjang">
                        <option value="semua" {{ old('jenjang', 'semua') == 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="s2" {{ old('jenjang') == 's2' ? 'selected' : '' }}>S2</option>
                        <option value="s3" {{ old('jenjang') == 's3' ? 'selected' : '' }}>S3</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="active" value="1"
                                checked>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0">
                            <label class="form-check-label" for="inactive">Inactive</label>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masterkategorijurnal.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection
