@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Kategori Video /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('mastervideocategory.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="title">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title" value="{{ old('title') }}"
                        required>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="summary">Ringkasan</label>
                    <textarea class="form-control" id="summary" name="summary" rows="4">{{ old('summary') }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ old('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="photo">Foto Kategori</label>
                    <input type="file" id="photo" class="form-control" name="photo" accept="image/*">
                    <small class="text-muted">Opsional. Format JPG, JPEG, PNG, WEBP. Max 2MB.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('mastervideocategory.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
@endsection
