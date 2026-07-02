<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span> Banner Utama
        </h4>

        @include('utils.modal')

        @can('banner_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('banner.create') }}">
                <i class="bx bx-plus"></i> Tambah
            </a>
        @endcan

        <div class="card">
            <h5 class="card-header">Daftar Banner Utama</h5>
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
