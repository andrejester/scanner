@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Banner Iklan /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('banner-ads.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label" for="title">Judul Banner <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title" value="{{ old('title') }}"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Posisi <span class="text-danger">*</span></label>
                    <select class="form-select" name="position" required>
                        <option value="">Pilih Posisi</option>
                        <option value="top" {{ old('position') == 'top' ? 'selected' : '' }}>Atas</option>
                        <option value="above_logo" {{ old('position') == 'above_logo' ? 'selected' : '' }}>Atas Logo
                        </option>
                        <option value="left" {{ old('position') == 'left' ? 'selected' : '' }}>Kiri</option>
                        <option value="right" {{ old('position') == 'right' ? 'selected' : '' }}>Kanan</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="image">Gambar Banner <span class="text-danger">*</span></label>
                    <input type="file" id="image" class="form-control" name="image" accept="image/*" required>
                    <small class="text-muted">Rekomendasi ukuran: 600x300px. Format: JPG, JPEG, PNG, WEBP. Max: 2MB</small>
                </div>

                <div class="col-md-8">
                    <label class="form-label" for="link">Link URL</label>
                    <input type="url" id="link" class="form-control" name="link" value="{{ old('link') }}"
                        placeholder="https://example.com">
                    <small class="text-muted">URL tujuan ketika banner diklik (opsional)</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Target Link <span class="text-danger">*</span></label>
                    <select class="form-select" name="target" required>
                        <option value="_blank" {{ old('target') == '_blank' ? 'selected' : '' }}>Tab Baru (_blank)</option>
                        <option value="_self" {{ old('target') == '_self' ? 'selected' : '' }}>Tab Sama (_self)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="order">Urutan</label>
                    <input type="number" id="order" class="form-control" name="order" value="{{ old('order', 0) }}"
                        min="0">
                    <small class="text-muted">Urutan tampilan (angka lebih kecil tampil lebih dulu)</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('banner-ads.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection
