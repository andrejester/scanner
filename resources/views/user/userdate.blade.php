<?php
$account = true;
?>
@extends('layouts.app')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">User /</span> Backdate User
    </h4>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form
                </div>
                <div class="card-body">
                    <form id="form1">
                        <div class="row">
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="username">Username</label>
                                <select id="username" name="username" class="select2 form-select">
                                    <option value="">Pilih Username</option>
                                    @foreach (App\Models\User::get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="username">Tanggal</label>
                                <input type ="date" name="date" value="{{ getTgl() }}" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label" for="description">Lama (Menit)</label>
                                <input type="number" class="form-control" id="old" name="old" value="60">
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label" for="description">Keterangan</label>
                                <input type="text" class="form-control" id="description" name="description"
                                    value="">
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary" id="tombolSave"
                            onclick="save('{{ route('user-date.store') }}','post')"><i class="bx bx-save"></i>Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Data History Backdate User</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {!! $dataTable->table() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addon_js')
    {!! $dataTable->scripts() !!}
@endsection
