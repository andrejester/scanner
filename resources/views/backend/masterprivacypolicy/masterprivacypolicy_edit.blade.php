@extends('layouts.app')

@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Privacy Policy /</span> Edit
    </h4>

    {{-- Error Handling --}}
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                <h6 class="alert-heading mb-1">Error</h6>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    @endif

    <div class="card mb-4">
        <form class="card-body" action="{{ route('masterprivacypolicy.update', $masterprivacypolicy->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                {{-- Title --}}
                <div class="col-md-12">
                    <label class="form-label" for="title">Judul</label>
                    <input type="text" id="title" class="form-control" name="title"
                        value="{{ old('title', $masterprivacypolicy->title) }}" required>
                </div>

                {{-- Content --}}
                <div class="col-md-12">
                    <label class="form-label" for="content">Isi Kebijakan Privasi</label>
                    <textarea id="content" class="form-control" name="content" rows="6" required>{{ old('content', $masterprivacypolicy->content) }}</textarea>
                </div>

                {{-- Status --}}
                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <div class="mt-2">
                        <select name="is_active" id="is_active" class="form-control">
                            <option value="1"
                                {{ old('is_active', $masterprivacypolicy->is_active) == 1 ? 'selected' : '' }}>Active
                            </option>
                            <option value="0"
                                {{ old('is_active', $masterprivacypolicy->is_active) == 0 ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masterprivacypolicy.index') }}" class="btn btn-label-secondary">Kembali</a>
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
