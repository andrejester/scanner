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
        <form class="card-body" action="{{ route('portofolio.update', $portofolio->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="company_name">Title</label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ $portofolio->title }}">
                </div>

                <div class="col mb-12">
                    <label class="form-check-label">Category</label>
                    <select name="cat_id" class="form-control" data-placeholder="Choose anything">
                        @foreach ($category as $value)
                            <option value="{{ $value->id }}" selected>{{ $value->id . ' - ' . $value->title }}</option>
                        @endforeach
                        @foreach ($categoryAll as $value)
                            <option value="{{ $value->id }}" selected>{{ $value->id . ' - ' . $value->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="companydescription_address">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description">{{ $portofolio->description }}</textarea>
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
                            value="{{ $portofolio->photo }}">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if (!empty($portofolio->photo))
                            <img src="{{ asset($portofolio->photo) }}" style="max-height: 100px;" />
                        @endif
                    </div>
                </div>
                <div class="col mb-12">
                    <label class="form-check-label">Status</label>
                    <div class="col mt-2">
                        <select name="aktif" class="form-control" id="aktif">
                            <option value="Y" {{ $portofolio->status == 'Y' ? 'selected' : '' }}>Active</option>
                            <option value="N" {{ $portofolio->status == 'N' ? 'selected' : '' }}>Inactive</option>
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
