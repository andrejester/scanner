<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span>Master Tagline
        </h4>

        @include('utils.modal')

        @can('mastertagline_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('mastertagline.create') }}"><i class="bx bx-plus"></i>Tambah</a>
        @endcan
        <div class="card">

            <h5 class="card-header">Daftar Master Tagline</h5>
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
