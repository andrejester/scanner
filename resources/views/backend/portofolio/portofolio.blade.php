<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">Home /</span>Portofolio
        </h4>

        @include('utils.modal')
        <!-- Ajax Sourced Server-side -->

        @can('portofolio_write')
            <a class="btn btn-primary mb-1 btn-sm " href="{{ route('portofolio.create') }}"><i
                    class="bx bx-plus"></i>Tambah</a>
        @endcan
        <div class="card">

            {{-- <livewire:transaksi.loan :customer="\App\Models\Master\Member::get()" wire:model="needsReinitialization" /> --}}
            <h5 class="card-header">Daftar Portofolio</h5>
            <div class="card-datatable text-nowrap">

                <div class="table-responsive">
                    {!! $dataTable->table() !!}
                </div>
            </div>
        </div>
        <!--/ Ajax Sourced Server-side -->
    @endsection
    @section('addon_js')
        {!! $dataTable->scripts() !!}
    @endsection
