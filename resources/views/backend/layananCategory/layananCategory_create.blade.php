@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Kategori Layanan ARSCORP /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('layananCategory.store') }}" method="post">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" id="title" class="form-control" name="title">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">Active</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="active" checked
                                value="active" wire:model="status">
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive"
                                wire:model="status">
                            <label class="form-check-label" for="inactive">Inactive</label>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>

        </form>
    </div>
@endsection
