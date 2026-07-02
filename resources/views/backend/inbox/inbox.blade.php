<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span>Inbox
        </h4>

        @include('utils.modal')
        <!-- Ajax Sourced Server-side -->
        <div class="card">
            {{-- <livewire:transaksi.loan :customer="\App\Models\Master\Member::get()" wire:model="needsReinitialization" /> --}}
            <h5 class="card-header">Daftar Inbox</h5>
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
