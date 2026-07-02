@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Kategori Video /</span> Edit
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
        <form class="card-body" action="{{ route('mastervideocategory.update', $category->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="title">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $category->title) }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="summary">Ringkasan</label>
                    <textarea class="form-control" id="summary" name="summary" rows="4">{{ old('summary', $category->summary) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="online" {{ old('type', $category->type) == 'online' ? 'selected' : '' }}>Online
                        </option>
                        <option value="offline" {{ old('type', $category->type) == 'offline' ? 'selected' : '' }}>
                            Offline</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="photo">Foto Kategori</label>
                    @if ($category->photo)
                        <div class="mb-2">
                            @php
                                $photoUrl = $category->photo;
                                if (!filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                                    $photoUrl = asset('storage/files/2/' . $category->photo);
                                }
                            @endphp
                            <img src="{{ $photoUrl }}" width="150" class="rounded">
                        </div>
                    @endif
                    <input type="file" id="photo" class="form-control" name="photo" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti. Format JPG, JPEG, PNG, WEBP. Max
                        2MB.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>
                            Inactive</option>
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
