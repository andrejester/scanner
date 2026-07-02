<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span> Alumni
        </h4>

        @include('utils.modal')

        @can('masteralumni_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('masteralumni.create') }}">
                <i class="bx bx-plus"></i> Tambah
            </a>
            <a class="btn btn-success btn-sm mb-1" href="{{ route('masteralumni.upload-form') }}">
                <i class="bx bx-upload"></i> Upload CSV
            </a>
        @endcan

        <div class="card">
            <h5 class="card-header">Daftar Alumni</h5>
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
