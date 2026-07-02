@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Penawaran Masuk /</span> Show
    </h4>

    <div class="card mb-4">
        <form class="card-body" method="POST">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="Subject">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" class="form-control" name="nama_lengkap"
                        value="{{ $penawaran->nama_lengkap }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Kota Domisili">Kota Domisili</label>
                    <input type="text" id="kota_domisili" class="form-control" name="kota_domisili"
                        value="{{ $penawaran->kota_domisili }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="No Whatsapp">No Whatsapp</label>
                    <input type="text" id="no_whatsapp" class="form-control" name="no_whatsapp"
                        value="{{ $penawaran->no_whatsapp }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Email">Email</label>
                    <input type="text" id="emil" class="form-control" name="email" value="{{ $penawaran->email }}"
                        readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="IP">IP</label>
                    <input type="text" id="ip" class="form-control" name="ip" value="{{ $penawaran->ip }}"
                        readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Browser">Browser</label>
                    <input type="text" id="browser" class="form-control" name="browser"
                        value="{{ $penawaran->browser }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="Lokasi">Lokasi</label>
                    <input type="text" id="location" class="form-control" name="location"
                        value="{{ $penawaran->location }}" readonly>
                </div>


                <div class="pt-4">
                    <button type="button" class="btn btn-label-secondary" onclick="window.history.back();">
                        Back
                    </button>
                </div>

        </form>
    </div>
@endsection

@push('custom_js')
@endpush
