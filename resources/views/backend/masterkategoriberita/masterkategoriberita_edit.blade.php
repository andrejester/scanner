@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Kategori Property /</span> Edit
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
        <form class="card-body" action="{{ route('masterkategoriberita.update', $masterkategoriberita->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="nama_kategori">Keterangan</label>
                    <input type="text" id="nama_kategori" class="form-control" name="nama_kategori"
                        value="{{ $masterkategoriberita->nama_kategori }}">
                </div>
                <div class="col mb-12">
                    <label class="form-check-label">Status</label>
                    <div class="col mt-2">
                        <select name="is_active" class="form-control" id="is_active">
                            <option value="active" {{ $masterkategoriberita->is_active == 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ $masterkategoriberita->is_active == 'inactive' ? 'selected' : '' }}>
                                Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>

        </form>
    </div>
@endsection
