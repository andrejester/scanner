@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Blog /</span> Edit
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
        <form class="card-body" action="{{ route('blogadmin.update', $blogadmin->id) }}" method="POST">
            @csrf
            @method('put')

            <div class="row g-3">

                {{-- Title --}}
                <div class="col-md-12">
                    <label class="form-label" for="title">Title</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-captions"></i></span>
                        <input type="text" class="form-control" name="title"
                            value="{{ old('title', $blogadmin->title) }}" placeholder="Judul Berita" />
                    </div>
                </div>

                {{-- Category --}}
                <div class="col mb-6">
                    <label class="form-label">Category</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-list-ul"></i></span>
                        <select name="post_cat_id" class="form-control">
                            @foreach ($categoryAll as $value)
                                <option value="{{ $value->id }}"
                                    {{ $value->id == optional($blogadmin->category)->id ? 'selected' : '' }}>
                                    {{ $value->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                {{-- Tags --}}
                <div class="col mb-6">
                    <label class="form-label">Tags</label>
                    <select name="tags[]" id="select2Info" class="select2 form-select" multiple>
                        @foreach ($taglines as $tag)
                            <option value="{{ $tag->id }}"
                                {{ in_array($tag->id, $blogadmin->tags ?? []) ? 'selected' : '' }}>
                                {{ $tag->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Summary --}}
                <div class="col-md-12">
                    <label class="form-label">Summary</label>
                    <textarea class="form-control" id="summary" name="summary" rows="3">{{ old('summary', $blogadmin->summary) }}</textarea>
                </div>

                {{-- Content --}}
                <div class="col-md-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="10">{{ old('description', $blogadmin->description) }}</textarea>
                </div>

                {{-- Thumbnail --}}
                <div class="col-md-12">
                    <label class="form-label">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="photo" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="photo" class="form-control" type="text" name="photo"
                            value="{{ old('photo', $blogadmin->photo) }}">
                    </div>

                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if ($blogadmin->photo)
                            <img src="{{ $blogadmin->photo }}" style="height: 100px;">
                        @endif
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status</label><br>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="active"
                            {{ $blogadmin->status == 'active' ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" value="inactive"
                            {{ $blogadmin->status == 'inactive' ? 'checked' : '' }}>
                        <label class="form-check-label">Inactive</label>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Update</button>
                    <a href="{{ route('blogadmin.index') }}" class="btn btn-label-secondary">Kembali</a>
                </div>

            </div>
        </form>
    </div>
@endsection

@push('custom_js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        $('#lfm').filemanager('image');
        $('#multiple-select-field').select2({
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            closeOnSelect: false,
        });

        CKEDITOR.on('instanceReady', function(evt) {
            var editor = evt.editor;

            editor.on('notificationShow', function(event) {
                var notification = event.data.notification;

                setTimeout(function() {
                    if (notification && notification.hide) {
                        notification.hide();
                    }
                }, 100);
            });
        });

        CKEDITOR.replace('description', {
            height: 300,
            // removeButtons: 'PasteFromWord',
            filebrowserBrowseUrl: "{{ route('ckeditor.files') }}",
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: "form"
        });

        CKEDITOR.replace('summary', {
            toolbar: [{
                    name: 'document',
                    items: ['Source', 'Print']
                },
                {
                    name: 'styles',
                    items: ['Format', 'Font', 'FontSize']
                },
                {
                    name: 'align',
                    items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                },
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic', 'Underline']
                },
                {
                    name: 'paragraph',
                    items: ['NumberedList', 'BulletedList']
                }
            ],
            height: 150
        });
    </script>
@endpush
