<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span> Download
        </h4>

        @include('utils.modal')

        @can('masterdownload_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('masterdownload.create') }}">
                <i class="bx bx-plus"></i> Tambah
            </a>
        @endcan
        @can('masterkategoridownload_read')
            <a class="btn btn-outline-secondary btn-sm mb-1" href="{{ route('masterkategoridownload.index') }}">
                <i class="bx bx-category"></i> Kelola Kategori
            </a>
        @endcan

        <div class="card">
            <h5 class="card-header">Daftar Download</h5>
            <div class="card-datatable text-nowrap">
                <div class="table-responsive">
                    {!! $dataTable->table() !!}
                </div>
            </div>
        </div>
    @endsection
    @section('addon_js')
        {!! $dataTable->scripts() !!}
    @endsection
</div>
