<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span>Kategori Layanan
        </h4>

        @include('utils.modal')
        <!-- Ajax Sourced Server-side -->

        @can('layananCategory_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('layananCategory.create') }}"><i
                    class="bx bx-plus"></i>Tambah</a>
        @endcan
        <div class="card">

            {{-- <livewire:transaksi.loan :customer="\App\Models\Master\Member::get()" wire:model="needsReinitialization" /> --}}
            <h5 class="card-header">Daftar Kategori Layanan ARSCORP</h5>
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
