@extends('layouts.app')
@section('content')

    <script type="text/javascript" src="{{ asset('txteditor/richtexteditor/rte.js') }}"></script>
    <script>
        RTE_DefaultConfig.url_base = 'richtexteditor';
    </script>
    <script type="text/javascript" src="{{ asset('txteditor/richtexteditor/plugins/all_plugins.js') }}"></script>

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Layanan Perusahaan /</span> Edit
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
        <form class="card-body" action="{{ route('layananPerusahaan.update', $layananPerusahaan->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ $layananPerusahaan->title }}">
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
                    <textarea class="form-control" id="description" name="description" rows="10">{!! $layananPerusahaan->description !!}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder"
                                class="btn btn-outline-secondary">
                                <i class="tf-icons bx bx-photo-album"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="photo"
                            value="{{ $layananPerusahaan->photo }}">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if (!empty($layananPerusahaan->photo))
                            <img src="{{ asset($layananPerusahaan->photo) }}" style="max-height: 100px;" />
                        @endif
                    </div>
                </div>

                <div class="col mb-12">
                    <label class="form-check-label">Status</label>
                    <div class="col mt-2">
                        <select name="status" class="form-control" id="status">
                            <option value="active" {{ $layananPerusahaan->status == 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ $layananPerusahaan->status == 'inactive' ? 'selected' : '' }}>
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

@push('custom_js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('image');
        $('#multiple-select-field').select2({
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false,
        });

        var editor1 = new RichTextEditor("#description");
    </script>
@endpush
