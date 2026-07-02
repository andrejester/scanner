@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Logo Perusahaan /</span> Edit
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
        <form class="card-body" action="{{ route('logoPerusahaan.update', $logoPerusahaan->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="company_name">Title</label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ $logoPerusahaan->title }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="companydescription_address">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description">{{ $logoPerusahaan->description }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="photo"
                            value="{{ $logoPerusahaan->photo }}">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if (!empty($logoPerusahaan->photo))
                            <img src="{{ asset($logoPerusahaan->photo) }}" style="max-height: 100px;" />
                        @endif
                    </div>
                </div>
                <div class="col mb-12">
                    <label class="form-check-label">Status</label>
                    <div class="col mt-2">
                        <select name="status" class="form-control" id="status">
                            <option value="active" {{ $logoPerusahaan->status == 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ $logoPerusahaan->status == 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
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

@push('custom_js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('image');
    </script>
@endpush
