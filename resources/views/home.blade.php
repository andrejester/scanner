@extends('layouts.app')

@section('content')
    {{-- ── ALERTS ─────────────────────────────────────────────────────────────── --}}
    @if (session('success') || session('success-storage'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') ?? session('success-storage') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── ROW 1 · WELCOME + QUICK ACTIONS ────────────────────────────────────── --}}
    <div class="row g-4 mb-4">

        {{-- Welcome --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="row g-0 h-100 align-items-center">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-1">
                                Selamat Datang, {{ auth()->user()->name }}! 🎉
                            </h5>
                            <p class="text-muted small mb-3">
                                Login sebagai
                                <span class="badge bg-label-primary">
                                    {{ auth()->user()->getRoleNames()->first() ?? 'Admin' }}
                                </span>
                                &nbsp;&bull;&nbsp;
                                <i class="bx bx-time-five me-1"></i>{{ now()->format('d M Y, H:i') }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="" class="btn btn-sm btn-primary">
                                    <i class="bx bx-user me-1"></i>Profile
                                </a>
                                <a href="https://boxicons.com" class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <i class="bx bx-link-external me-1"></i>Ikon
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5 pb-0 text-center">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="155"
                            class="scaleX-n1-rtl" alt="Welcome">
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header border-0 pb-1">
                    <h6 class="text-muted fw-semibold mb-0">
                        <i class="bx bx-zap me-1"></i>Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 h-100">
                        <div class="col-6">
                            <div
                                class="h-100 d-flex flex-column align-items-center justify-content-center gap-2 rounded border p-3 text-center">
                                <span class="badge bg-label-success rounded p-2">
                                    <i class="bx bx-refresh bx-sm"></i>
                                </span>
                                <a href="{{ route('admin.clearCache') }}" class="btn btn-sm btn-outline-success w-100">Cache
                                    Clear</a>
                                <small class="text-muted">Bersihkan Cache</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div
                                class="h-100 d-flex flex-column align-items-center justify-content-center gap-2 rounded border p-3 text-center">
                                <span class="badge bg-label-warning rounded p-2">
                                    <i class="bx bx-link bx-sm"></i>
                                </span>
                                <a href="{{ route('admin.storageLink') }}"
                                    class="btn btn-sm btn-outline-warning w-100">Storage Link</a>
                                <small class="text-muted">Buat Pintasan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /ROW 1 --}}
@endsection
