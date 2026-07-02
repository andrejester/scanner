@extends('layouts.app')
@section('content')
    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role" id="modalContent">
        </div>
    </div>
    <h4 class="py-3 mb-2">Daftar Peran</h4>

    <p>
        Peran memberikan akses ke menu dan fitur yang telah ditentukan sehingga tergantung pada peran yang diberikan,
        seorang administrator dapat memiliki akses sesuai kebutuhan pengguna.</p>
    <!-- Role cards -->

    <div class="row g-4">
        @foreach ($roles as $role)
            <?php
            $totalUsers = $role->users()->count();
            // echo $totalUsers . $role->name;
            ?>
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="fw-normal">Total {{ $totalUsers }} users</h6>
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    title="Vinnie Mostowy" class="avatar avatar-sm pull-up">
                                    <img class="rounded-circle" src="../../assets/img/avatars/5.png" alt="Avatar">
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="role-heading">
                                <h4 class="mb-1">{{ $role->name }}</h4>

                            </div>
                            <a href="javascript:void(0);" class="text-muted"><i class="bx bx-copy"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @can('permission_create')
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="row h-100">
                        <div class="col-sm-5">
                            <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-3">
                                <img src="../../assets/img/illustrations/sitting-girl-with-laptop-light.png" class="img-fluid"
                                    alt="Image" width="120"
                                    data-app-light-img="illustrations/sitting-girl-with-laptop-light.png"
                                    data-app-dark-img="illustrations/sitting-girl-with-laptop-dark.png">
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="card-body text-sm-end text-center ps-sm-0">

                                <button class="btn btn-primary mb-3 text-nowrap "
                                    onclick="openModal('{{ route('permission.create') }}')">
                                    Tambahkan peran</button>
                                <p class="mb-0">
                                    Tambahkan peran jika belum ada.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        <div class="col-12">
            <!-- Role Table -->
            <div class="card">
                <div class="card-datatable table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
            <!--/ Role Table -->
        </div>
    </div>
    <!--/ Role cards -->
@endsection
@section('addon_js')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endsection
