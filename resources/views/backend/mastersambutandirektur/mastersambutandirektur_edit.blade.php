@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Sambutan Direktur /</span> Edit
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
        <form class="card-body" action="{{ route('mastersambutandirektur.update', $mastersambutandirektur->id) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label" for="nama_direktur">Nama Direktur</label>
                    <input type="text" id="nama_direktur" class="form-control" name="nama_direktur"
                        value="{{ old('nama_direktur', $mastersambutandirektur->nama_direktur) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="jabatan">Jabatan</label>
                    <input type="text" id="jabatan" class="form-control" name="jabatan"
                        value="{{ old('jabatan', $mastersambutandirektur->jabatan) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="sambutan">Sambutan</label>
                    <textarea class="form-control" id="sambutan" name="sambutan" rows="10">{{ old('sambutan', $mastersambutandirektur->sambutan) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="foto"
                            value="{{ old('thumbnail', $mastersambutandirektur->foto) }}">
                    </div>

                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if ($mastersambutandirektur->foto)
                            <img src="{{ $mastersambutandirektur->foto }}" style="height: 100px;">
                        @endif
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control" id="is_active">
                        <option value="1" {{ $mastersambutandirektur->is_active == 1 ? 'selected' : '' }}>Active
                        </option>
                        <option value="0" {{ $mastersambutandirektur->is_active == 0 ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('mastersambutandirektur.index') }}" class="btn btn-label-secondary">Batal</a>
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

        CKEDITOR.replace('sambutan', {
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
