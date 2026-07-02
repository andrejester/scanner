@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Master Pelatihan /</span> Tambah
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
        <form id="form1" class="card-body" action="{{ route('masterpelatihan.store') }}" method="post">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="kode">Kode Pelatihan</label>
                    <input type="text" id="kode" class="form-control" value="{{ $kode }}" name="kode"
                        placeholder="Contoh P001" readonly>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="keterangan">Kategori Pelatihan</label>
                    <input type="text" id="keterangan" class="form-control" name="keterangan" placeholder="Kategori">
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-label-secondary"></button>
                </div>
            </div>
        </form>
    </div>
@endsection
