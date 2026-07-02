<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span> Event
        </h4>

        @include('utils.modal')

        @can('masterevent_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('masterevent.create') }}">
                <i class="bx bx-plus"></i> Tambah
            </a>
        @endcan

        <div class="card">
            <h5 class="card-header">Daftar Event</h5>
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
