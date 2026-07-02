@extends('layouts.app')
@section('content')



    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Brand Perusahaan /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('mastersupportteam.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" id="title" class="form-control" name="title">
                </div>

                <div class="col mb-12">
                    <label class="form-check-label">Category</label>
                    <select name="cat_id" class="form-control" data-placeholder="Choose anything">
                        @foreach ($categoryAll as $value)
                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="photo">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;"></div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="status">Status</label>
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

@push('custom_js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('image');
        $('#multiple-select-field').select2({
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false,
        });
    </script>
@endpush
