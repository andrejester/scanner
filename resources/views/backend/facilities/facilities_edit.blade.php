@extends('layouts.app')
@section('content')

    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Property /</span> Edit
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
        <form class="card-body" action="{{ route('facilities.update', $facilities->id) }}" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="name">Nama</label>
                    <input type="text" id="name" class="form-control" name="name"
                        value="{{ old('name', $facilities->name) }}">
                </div>

                <div class="col mb-12">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-control">
                        <option value="{{ $facilities->category }}" selected>{{ $facilities->category }}</option>
                        <option value="">-----</option>
                        <option value="ARS Property">ARS Property</option>
                        <option value="Ashraya House">Ashraya House</option>
                    </select>
                </div>


                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="10">{{ old('description', $facilities->description) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" class="form-control" name="status">
                        <option value="active" {{ $facilities->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $facilities->status == 'inactive' ? 'selected' : '' }}>Inactive
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('custom_js')
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
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
    </script>
@endpush
