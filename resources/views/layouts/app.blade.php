<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Aplikasi URL -->
    <meta name="app-url" content="{{ url('/') }}">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard Admin</title>

    {{-- <meta name="description" content="Most Powerful &amp; Comprehensive Bootstrap 5 HTML Admin Dashboard Template built for developers!" /> --}}
    {{-- <meta name="keywords" content="dashboard, bootstrap 5 dashboard, bootstrap 5 design, bootstrap 5"> --}}

    <!-- Tambahkan tag favicon di sini -->
    @php
        $setting = settings();
    @endphp
    @if (!empty($setting->company_favicon))
        <link rel="icon" href="{{ asset($setting->company_favicon) }}" type="image/x-icon">
    @else
        <link rel="icon" href="{{ asset('storage/logo-title.svg') }}" type="image/x-icon">
    @endif

    <!-- include libraries(jQuery, bootstrap) -->
    {{-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet"> --}}
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <link rel="stylesheet" href=" {{ asset('txteditor/res/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('txteditor/richtexteditor/rte_theme_default.css') }}" />

    <!-- include summernote css/js -->


    @foreach ($pilihCss as $value)
        <?php echo $css[$value] . "\n"; ?>
    @endforeach

    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css"> --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/fullcalender/fullcalendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-calendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/card-analytics.css') }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="{{ asset('richtexteditor/rte_theme_default.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js ) }}"in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.) }}"  -->
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    {{-- .customizer-hide #template-customizer .template-customizer-open-btn{display:true/none} --}}
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        .mb-6 {
            margin-bottom: 1.5rem !important;
        }

        .p-6 {
            padding: 1.5rem !important;
        }

        @keyframes notif-bounce {
            0% {
                transform: scale(1);
            }

            30% {
                transform: scale(1.3);
            }

            50% {
                transform: scale(0.9);
            }

            70% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .notif-animate {
            animation: notif-bounce 0.6s ease;
        }

        @keyframes notif-blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }

            100% {
                opacity: 1;
            }
        }

        .notif-blink {
            animation: notif-blink 1s ease-in-out 2;
        }

        @keyframes bell-shake {
            0% {
                transform: rotate(0);
            }

            10% {
                transform: rotate(18deg);
            }

            20% {
                transform: rotate(-18deg);
            }

            30% {
                transform: rotate(14deg);
            }

            40% {
                transform: rotate(-14deg);
            }

            50% {
                transform: rotate(10deg);
            }

            60% {
                transform: rotate(-10deg);
            }

            70% {
                transform: rotate(6deg);
            }

            80% {
                transform: rotate(-6deg);
            }

            90% {
                transform: rotate(3deg);
            }

            100% {
                transform: rotate(0);
            }
        }

        .bell-animate {
            animation: bell-shake 0.6s ease-in-out 5;
            transform-origin: top center;
        }
    </style>

    @livewireStyles

    <script type="text/javascript" src="{{ asset('richtexteditor/rte.js') }}"></script>
    <script type="text/javascript" src="{{ asset('richtexteditor/plugins/all_plugins.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


</head>

<body>


    <!-- ?PROD Only: Google Tag Manager (noscript) (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DDHKGP" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu -->

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">


                <div class="app-brand demo">
                    <a href="{{ route('dashboard') }}" class="app-brand-link">
                        <span class="app-brand-logo demo">

                            <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink">
                                <defs>
                                    <path
                                        d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                                        id="path-1"></path>
                                    <path
                                        d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                                        id="path-3"></path>
                                    <path
                                        d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                                        id="path-4"></path>
                                    <path
                                        d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                                        id="path-5"></path>
                                </defs>
                                <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                                        <g id="Icon" transform="translate(27.000000, 15.000000)">
                                            <g id="Mask" transform="translate(0.000000, 8.000000)">
                                                <mask id="mask-2" fill="white">
                                                    <use xlink:href="#path-1"></use>
                                                </mask>
                                                <use fill="#696cff" xlink:href="#path-1"></use>
                                                <g id="Path-3" mask="url(#mask-2)">
                                                    <use fill="#696cff" xlink:href="#path-3"></use>
                                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                                </g>
                                                <g id="Path-4" mask="url(#mask-2)">
                                                    <use fill="#696cff" xlink:href="#path-4"></use>
                                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                                </g>
                                            </g>
                                            <g id="Triangle"
                                                transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                                <use fill="#696cff" xlink:href="#path-5"></use>
                                                <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>

                        </span>
                        <span class="app-brand-text demo menu-text fw-bold ms-2">Admin Panel</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                @include('layouts.sidebar')


            </aside>
            <!-- / Menu -->
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0 d-xl-none me-3">
                        <a class="nav-item nav-link me-xl-4 px-0" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item navbar-search-wrapper mb-0">
                                <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                                    <i class="bx bx-search bx-sm"></i>
                                    <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                                </a>
                            </div>
                        </div>
                        <!-- /Search -->
                        <ul class="navbar-nav align-items-center ms-auto flex-row">

                            <li><span class="badge bg-label-primary px-2">{{ tanggalIndonesia(getTgl()) }}</span>
                            </li>

                            <!-- Style Switcher -->
                            <li class="nav-item dropdown-style-switcher dropdown me-xl-0 me-2">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <i class='bx bx-sm'></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                                            <span class="align-middle"><i class='bx bx-sun me-2'></i>Light</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                                            <span class="align-middle"><i class="bx bx-moon me-2"></i>Dark</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                                            <span class="align-middle"><i class="bx bx-desktop me-2"></i>System</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- / Style Switcher-->

                            <!-- Notifications -->
                            {{-- @include('layouts.partials.notifikasi') --}}
                            <!-- /Notifications -->


                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/user.png') }}" alt
                                            class="w-px-40 rounded-circle h-auto">
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="pages-account-settings-account.html">
                                            <div class="d-flex">
                                                <div class="me-3 flex-shrink-0">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('assets/img/user.png') }}" alt
                                                            class="w-px-40 rounded-circle h-auto">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-medium d-block">{{ Auth::user()->name }}</span>
                                                    <small
                                                        class="text-muted">{{ Auth::user()->getRoleNames() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile-user.index') }}">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    @can('setting_read')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('setting.index') }}">
                                                <i class="bx bx-cog me-2"></i>
                                                <span class="align-middle">Settings</span>
                                            </a>
                                        </li>
                                    @endcan
                                    <li>
                                        <a class="dropdown-item" target="_blank" onclick="logout(event)">

                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Log Out</span>

                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->


                        </ul>
                    </div>

                    <!-- Search Small Screens -->
                    <div class="navbar-search-wrapper search-input-wrapper d-none">
                        <input type="text" class="form-control search-input container-xxl border-0"
                            placeholder="Search..." aria-label="Search...">
                        <i class="bx bx-x bx-sm search-toggler cursor-pointer"></i>
                    </div>
                </nav>

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                </div>
                <!-- Toast with Animation -->
                <div class="bs-toast toast toast-ex animate__animated my-2" role="alert" aria-live="assertive"
                    aria-atomic="true" data-bs-delay="2000" id="toastId">
                    <div class="toast-header">
                        <i class='bx bx-bell me-2'></i>
                        <div class="fw-medium toast-header-info me-auto">Bootstrap</div>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                    </div>
                </div>

                <!-- Footer -->
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl d-flex justify-content-between flex-md-row flex-column flex-wrap py-2">
                        <div class="mb-md-0 mb-2">
                            Copyright ©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>. <a href="#" target="_blank"
                                class="footer-link fw-medium">
                                All Rights Reserved</a>
                        </div>
                    </div>
                </footer>
                <!-- / Footer -->


                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->


    @foreach ($pilihJs as $value)
        <script src="{{ asset($js[$value]) }}"></script>
    @endforeach

    @livewireScripts



    @yield('addon_js')
    @stack('custom_js')
    @include('sweetalert::alert', ['cdn' => 'https://cdn.jsdelivr.net/npm/sweetalert2@9'])

    {{-- Web Push Notification --}}
    @include('layouts.partials.web-push')

</body>


</html>
