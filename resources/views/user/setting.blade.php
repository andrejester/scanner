@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">System /</span> Global Setting
    </h4>

    <div class="card mb-4">
        <form class="card-body" id="form1">
            @csrf
            <h6>1. Info Perusahaan</h6>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="company_name">Nama Perusahaan</label>
                    <input type="text" id="company_name" class="form-control" name="company_name"
                        value="{{ $setting->company_name }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label" for="company_address">Alamat</label>
                    <input type="text" id="company_address" class="form-control" name="company_address"
                        value="{{ $setting->company_address }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="company_email">Email</label>
                    <input type="text" id="company_email" class="form-control" name="company_email"
                        value="{{ $setting->company_email }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="company_phone">Telepon</label>
                    <input type="text" id="company_phone" class="form-control phone-mask" name="company_phone"
                        value="{{ $setting->company_phone }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="company_whatsapp">Whatsapp</label>
                    <input type="text" id="company_whatsapp" class="form-control" name="company_whatsapp"
                        value="{{ $setting->company_whatsapp }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="company_ig">Instagram</label>
                    <input type="text" id="company_ig" class="form-control instagram" name="company_ig"
                        value="{{ $setting->company_ig }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="company_admin">Admin</label>
                    <input type="text" id="company_admin" class="form-control phone-mask" name="company_admin"
                        value="{{ $setting->company_admin }}">
                    <div id="floatingInputHelp" class="form-text"><a href="#">Jika Lebih dari satu
                            pisahkan dengan ; (089xxx;081)</a></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="company_youtube">Youtube</label>
                    <input type="text" id="company_youtube" class="form-control youtube" name="company_youtube"
                        value="{{ $setting->company_youtube }}">
                </div>


                <div class="col-md-12">
                    <label class="form-label" for="company_maps">Maps</label>
                    <input type="text" id="company_maps" class="form-control" name="company_maps"
                        value="{{ $setting->company_maps }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_website">Website</label>
                    <input type="text" id="company_website" class="form-control" name="company_website"
                        value="{{ $setting->company_website }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_summary">Summary</label>
                    <textarea class="form-control" id="company_summary" name="company_summary" rows="3">{{ $setting->company_summary }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_deskripsi">Deskripsi</label>
                    <textarea class="form-control" id="company_deskripsi" name="company_deskripsi" rows="10">{{ $setting->company_deskripsi }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_visi">Visi</label>
                    <textarea class="form-control" id="company_visi" name="company_visi" rows="10">{{ $setting->company_visi }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label" for="company_misi">Misi</label>
                    <textarea class="form-control" id="company_misi" name="company_misi" rows="10">{{ $setting->company_misi }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_photo">Photo</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary"
                                style="color: white">
                                <i class="bx bx-image-add"> </i> Choose
                            </a>
                        </span>
                        <input id="thumbnail" class="form-control" type="text" name="company_photo"
                            value="{{ $setting->company_photo }}" readonly>
                    </div>
                    <div id="holder" style="margin-top: 15px; max-height: 100px;">
                        @if (!empty($setting->company_photo))
                            <img src="{{ asset($setting->company_photo) }}" style="max-height: 100px;" />
                        @endif
                    </div>
                    @error('company_photo')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_favicon">Favicon (Icon Website)</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm-favicon" data-input="thumbnail-favicon" data-preview="holder-favicon"
                                class="btn btn-primary" style="color: white">
                                <i class="bx bx-image-add"> </i> Choose
                            </a>
                        </span>
                        <input id="thumbnail-favicon" class="form-control" type="text" name="company_favicon"
                            value="{{ $setting->company_favicon }}" readonly>
                    </div>
                    <div id="holder-favicon" style="margin-top: 15px; max-height: 100px;">
                        @if (!empty($setting->company_favicon))
                            <img src="{{ asset($setting->company_favicon) }}" style="max-height: 100px;" />
                        @endif
                    </div>
                    <div class="form-text">
                        <small>Format yang disarankan: .ico, .png (32x32px atau 64x64px)</small>
                    </div>
                    @error('company_favicon')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="company_file">File Dokumen</label>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <a id="lfm-doc" data-input="thumbnail-doc" data-preview="holder-doc"
                                class="btn btn-primary" style="color: white">
                                <i class="bx bxs-file-pdf"> </i> Choose
                            </a>
                        </span>
                        <input id="thumbnail-doc" class="form-control" type="text" name="company_file"
                            value="{{ $setting->company_file }}" readonly>
                    </div>
                    <div id="holder-doc" style="margin-top: 15px; max-height: 100px;"></div>
                </div>

                <div class="pt-4">
                    <button type="button" onclick="save('{{ route('setting.update', $setting->id) }}','put')"
                        class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
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
        $('#lfm-favicon').filemanager('image');

        $(document).ready(function() {
            $('#lfm-doc').filemanager('file');
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

        CKEDITOR.replace('company_deskripsi', {
            height: 300,
            // removeButtons: 'PasteFromWord',
            filebrowserBrowseUrl: "{{ route('ckeditor.files') }}",
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: "form"
        });

        CKEDITOR.replace('company_visi', {
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

        CKEDITOR.replace('company_misi', {
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

        CKEDITOR.replace('company_summary', {
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
