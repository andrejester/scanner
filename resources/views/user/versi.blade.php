@extends('layouts.app')
@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Aplikasi /</span> Versi
    </h4>

    <div class="row">
        <!-- Timeline Basic-->
        <div class="col-lg-6">
            <div class="card">
                <h5 class="card-header">List history update</h5>
                <div class="card-body">
                    <ul class="timeline mb-0">
                        @php
                            function getRandomClass()
                            {
                                $classes = ['primary', 'danger', 'warning', 'info', 'success'];
                                return $classes[array_rand($classes)];
                            }
                            $version = '1.0.0';
                        @endphp
                        @foreach ($update as $value)
                            @php
                                $version = incrementVersion($version);
                            @endphp
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-{{ getRandomClass() }}"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Log</h6>
                                        <small
                                            class="text-muted">{{ Carbon\Carbon::parse($value['date'])->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">
                                        {{ $value['message'] }}
                                    </p>
                                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                        <div class="d-flex flex-wrap align-items-center mb-50">
                                            <div class="avatar avatar-sm me-2">
                                                <img src="{{ asset('assets/img/user.png') }}" alt="Avatar"
                                                    class="rounded-circle" />
                                            </div>
                                            <div>
                                                <p class="mb-0 small fw-medium">{{ $value['author'] }}</p>
                                                <span class="badge bg-{{ getRandomClass() }}">Versi
                                                    {{ $value['version'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6 h-100">
            <div class="card shadow-none bg-label-primary h-100">
                <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                    <div
                        class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start align-items-start">
                        <div class="card-title">
                            <h5 class="text-primary mb-2">Catatan update informasi</h5>
                            <p class="text-body w-sm-80 app-academy-xl-100">
                                Halamain ini merupakan informasi dari update / perbaikan sistem, yang berisi Informasi
                                Penting
                            </p>
                        </div>
                    </div>
                    <div
                        class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                        <img class="img-fluid scaleX-n1-rtl"
                            src="{{ asset('assets/img/illustrations/boy-app-academy.png') }}" alt="boy illustration" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--/ Ajax Sourced Server-side -->
@endsection
