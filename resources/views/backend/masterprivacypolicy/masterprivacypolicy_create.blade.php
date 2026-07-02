@extends('layouts.app')

@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Privacy Policy /</span> Tambah
    </h4>

    {{-- Error handling --}}
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading mb-1">Error</h6>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    @endif

    <div class="card mb-4">
        <form id="form1" class="card-body" action="{{ route('masterprivacypolicy.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                {{-- Title --}}
                <div class="col-md-12">
                    <label class="form-label" for="title">Judul</label>
                    <input type="text" id="title" class="form-control" name="title" value="{{ old('title') }}"
                        placeholder="Masukkan judul kebijakan privasi" required>
                </div>

                {{-- Content --}}
                <div class="col-md-12">
                    <label class="form-label" for="content">Isi Kebijakan Privasi</label>
                    <textarea id="content" class="form-control" name="content" rows="6" placeholder="Tulis isi kebijakan privasi..."
                        required>{{ old('content') }}</textarea>
                </div>

                {{-- Status --}}
                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="active" value="1"
                                {{ old('is_active', 1) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0"
                                {{ old('is_active') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="inactive">Inactive</label>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $('#content').summernote({
            height: 250,
            placeholder: 'Tulis isi kebijakan privasi di sini...'
        });
    </script>
@endpush
