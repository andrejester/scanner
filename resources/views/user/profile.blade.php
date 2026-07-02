@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-xl-4 col-lg-5 order-md-0 order-1">
            <!-- User Card -->
            <div class="card mb-6">
                <div class="card-body pt-12">
                    <div class="user-avatar-section">
                        <div class="d-flex align-items-center flex-column">
                            <img class="img-fluid mb-4 rounded" src="{{ asset('assets/img/user.png') }}" height="120"
                                width="120" alt="User avatar" />
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ Auth::user()->name }}</h4>
                                <span class="badge bg-label-secondary">{{ Auth::user()->getRoleNames() }}</span>
                            </div>
                        </div>
                    </div>
                    <h5 class="border-bottom mb-4 pb-2">Details</h5>
                    <div class="info-container">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <span class="fw-medium me-2">Nama :</span>
                                <span>{{ Auth::user()->name }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium me-2">Email :</span>
                                <span>{{ Auth::user()->email }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium me-2">Status:</span>
                                <span class="badge bg-label-success">Aktif</span>
                            </li>
                        </ul>
                        <div class="mb-4">
                            <img src="data:image/svg+xml;base64,{{ $qrCodeImage }}" alt="QR Code" class="img-fluid">
                            <p class="text-muted mt-2">Gunakan aplikasi <a
                                    href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&pcampaignid=web_share"
                                    target="_blank">
                                    Google Authenticator </a>
                                untuk memindai QR code ini.</p>
                        </div>
                        <div class="d-flex justify-content-center pt-3">
                            <a href="javascript:;" class="btn btn-primary me-3" data-bs-target="#editUser"
                                data-bs-toggle="modal">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /User Card -->
        </div>
        <!--/ User Sidebar -->
        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <!-- User Pills -->
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i
                            class="bx bx-lock-alt me-1"></i>Keamanan</a></li>
            </ul>
            <!--/ User Pills -->

            <!-- Change Password -->
            <div class="card mb-4">
                <h5 class="card-header">Ganti Password</h5>
                <div class="card-body">
                    <form id="formChangePassword" method="post" action="{{ route('profile-user.store') }}">
                        @csrf
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading mb-1">Pastikan Password Baru Sesuai Dengan Persyaratan</h6>
                            <span>Minimal 8 Karakter, Mengandung Huruf Besar & Simbol</span>
                        </div>
                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    <h6 class="alert-heading mb-1">Error</h6>
                                    <span>{{ $error }}</span>
                                </div>
                            @endforeach
                        @endif

                        <div class="row">
                            <div class="col-12 col-sm-6 form-password-toggle mb-3">
                                <label class="form-label" for="old_password">Password Lama</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" id="old_password" name="old_password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 form-password-toggle mb-3">
                                <label class="form-label" for="password">Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" id="password" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 form-password-toggle mb-3">
                                <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="password_confirmation"
                                        id="password_confirmation"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">Ganti Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--/ Change Password -->
        </div>
        <!--/ User Content -->
    </div>
    <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content p-md-5 p-3">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="mb-4 text-center">
                        <h3>Edit Informasi User</h3>
                    </div>
                    <form id="form1" class="row g-3">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Nama</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Nama" value="{{ Auth::user()->name }}" />
                            </div>
                            {{-- <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Email" required />
                            </div> --}}

                        </div>
                        <div class="col-12 text-center">
                            <button type="button"
                                onclick="save('{{ route('profile-user.update', Auth::user()->id) }}','put')"
                                class="btn btn-primary me-sm-3 me-1">Submit</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
