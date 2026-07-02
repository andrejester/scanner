@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Blog /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('blogadmin.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="title">Title</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon11"><i class="icon-base bx bx-captions"></i></span>
                        <input type="text" class="form-control" name="title" placeholder="Judul Berita"
                            aria-label="Username" aria-describedby="basic-addon11" />
                    </div>
                </div>

                <div class="col mb-6">
                    <label class="form-label">Category</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon11"><i class="icon-base bx bx-list-ul"></i></span>
                        <select name="post_cat_id" id="kategori" class="form-control" data-placeholder="Choose anything">
                            @foreach ($categoryAll as $value)
                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col mb-6">
                    {{-- @dd($taglines) --}}
                    <label for="select2Info" class="form-label">Tags</label>
                    <div class="select2-info">
                        <select name="tags[]" id="select2Info" class="select2 form-select" multiple>
                            @foreach ($taglines as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="summary">Summary</label>
                    <div id="div_editor2">
                        <textarea class="form-control" id="summary" name="summary" rows="3"></textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <div id="div_editor1">
                        <textarea class="form-control" id="description" name="description" rows="10"></textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="photo">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="photo" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="photo" class="form-control" type="text" name="photo">
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;"></div>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="status">Status</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="active" checked
                                value="active">
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive">
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const el = document.getElementById('select-tags');
            if (el) {
                new Choices(el, {
                    removeItemButton: true,
                    placeholderValue: 'Pilih tags',
                    searchPlaceholderValue: 'Cari tags...',
                });
            }
        });
    </script>
@endpush
