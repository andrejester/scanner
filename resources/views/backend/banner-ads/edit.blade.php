@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Banner Iklan /</span> Edit
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
        <form class="card-body" action="{{ route('banner-ads.update', $bannerAds->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label" for="title">Judul Banner <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $bannerAds->title) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Posisi <span class="text-danger">*</span></label>
                    <select class="form-select" name="position" required>
                        <option value="">Pilih Posisi</option>
                        <option value="top" {{ old('position', $bannerAds->position) == 'top' ? 'selected' : '' }}>Atas
                        </option>
                        <option value="above_logo"
                            {{ old('position', $bannerAds->position) == 'above_logo' ? 'selected' : '' }}>Atas Logo
                        </option>
                        <option value="left" {{ old('position', $bannerAds->position) == 'left' ? 'selected' : '' }}>Kiri
                        </option>
                        <option value="right" {{ old('position', $bannerAds->position) == 'right' ? 'selected' : '' }}>
                            Kanan</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="image">Gambar Banner</label>
                    @if ($bannerAds->image)
                        <div class="mb-2">
                            @php
                                $imageUrl = $bannerAds->image;
                                if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                                    $imageUrl = asset('storage/files/2/' . $imageUrl);
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" width="300" class="d-block rounded">
                        </div>
                    @endif
                    <input type="file" id="image" class="form-control" name="image" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar. Rekomendasi ukuran: 600x300px.
                        Format: JPG, JPEG, PNG, WEBP. Max: 2MB</small>
                </div>

                <div class="col-md-8">
                    <label class="form-label" for="link">Link URL</label>
                    <input type="url" id="link" class="form-control" name="link"
                        value="{{ old('link', $bannerAds->link) }}" placeholder="https://example.com">
                    <small class="text-muted">URL tujuan ketika banner diklik (opsional)</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Target Link <span class="text-danger">*</span></label>
                    <select class="form-select" name="target" required>
                        <option value="_blank" {{ old('target', $bannerAds->target) == '_blank' ? 'selected' : '' }}>Tab
                            Baru (_blank)</option>
                        <option value="_self" {{ old('target', $bannerAds->target) == '_self' ? 'selected' : '' }}>Tab
                            Sama
                            (_self)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="order">Urutan</label>
                    <input type="number" id="order" class="form-control" name="order"
                        value="{{ old('order', $bannerAds->order) }}" min="0">
                    <small class="text-muted">Urutan tampilan (angka lebih kecil tampil lebih dulu)</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $bannerAds->is_active) ? 'checked' : '' }}>
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
