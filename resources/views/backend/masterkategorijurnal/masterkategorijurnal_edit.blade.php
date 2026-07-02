@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Kategori Jurnal /</span> Edit
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
        <form class="card-body" action="{{ route('masterkategorijurnal.update', $masterkategorijurnal->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">

                <div class="col-md-8">
                    <label class="form-label" for="nama">Nama Kategori</label>
                    <input type="text" id="nama" class="form-control" name="nama"
                        value="{{ old('nama', $masterkategorijurnal->nama) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="jenjang">Jenjang</label>
                    <select id="jenjang" class="form-control" name="jenjang">
                        @foreach (['semua' => 'Semua', 's2' => 'S2', 's3' => 'S3'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('jenjang', $masterkategorijurnal->jenjang) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $masterkategorijurnal->deskripsi) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $masterkategorijurnal->is_active == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $masterkategorijurnal->is_active == 0 ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <a href="{{ route('masterkategorijurnal.index') }}" class="btn btn-label-secondary">Batal</a>
                </div>

            </div>
        </form>
    </div>
@endsection
