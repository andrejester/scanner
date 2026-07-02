@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Portofolio /</span> Edit
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
        <form id="form1" class="card-body" action="{{ route('mastergaleri.update', $mastergaleri->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label" for="title">Judul <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $mastergaleri->title) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="category_id">Kategori</label>
                    <select id="category_id" class="form-control" name="category_id">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $mastergaleri->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $mastergaleri->description) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="photo">Foto</label>
                    <input type="file" id="photo" class="form-control" name="photo" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. Format: JPG, JPEG, PNG, WEBP. Maks:
                        2MB</small>
                    @if ($mastergaleri->photo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/files/2/' . $mastergaleri->photo) }}" alt="Foto saat ini"
                                height="80" class="rounded border">
                            <small class="d-block text-muted mt-1">Foto saat ini</small>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="aktif" id="active" value="Y"
                                {{ old('aktif', $mastergaleri->aktif) == 'Y' ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="aktif" id="inactive" value="N"
                                {{ old('aktif', $mastergaleri->aktif) == 'N' ? 'checked' : '' }}>
                            <label class="form-check-label" for="inactive">Nonaktif</label>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('mastergaleri.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection
