<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dreams LMS</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('frontend/assets/img/favicon.svg') }}">

    <script src="{{ asset('frontend/assets/js/theme-script.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/fontawesome/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.theme.default.min.css') }}">

    <link rel="stylesheet" href="https://dreamslms.dreamstechnologies.com/html/assets/plugins/feather/feather.css">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}">
</head>

<body>

    <div class="main-wrapper log-wrap">
        <div class="row">

            <div class="col-md-6 login-bg">
                <div class="owl-carousel login-slide owl-theme">
                    <div class="welcome-login">
                        <div class="login-banner">
                            <img src="https://dreamslms.dreamstechnologies.com/html/assets/img/login-img.png"
                                class="img-fluid" alt="Logo">
                        </div>
                        <div class="mentor-course text-center">
                            <h2>Welcome to <br>DreamsLMS Courses.</h2>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt
                                ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>
                    </div>
                    <div class="welcome-login">
                        <div class="login-banner">
                            <img src="https://dreamslms.dreamstechnologies.com/html/assets/img/login-img.png"
                                class="img-fluid" alt="Logo">
                        </div>
                        <div class="mentor-course text-center">
                            <h2>Welcome to <br>DreamsLMS Courses.</h2>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt
                                ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>
                    </div>
                    <div class="welcome-login">
                        <div class="login-banner">
                            <img src="https://dreamslms.dreamstechnologies.com/html/assets/img/login-img.png"
                                class="img-fluid" alt="Logo">
                        </div>
                        <div class="mentor-course text-center">
                            <h2>Welcome to <br>DreamsLMS Courses.</h2>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt
                                ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 login-wrap-bg">

                <div class="login-wrapper">
                    <div class="loginbox">
                        <div class="w-100">
                            <div class="img-logo">
                                <img src="{{ asset('frontend/assets/img/logo.svg') }}" class="img-fluid" alt="Logo">
                                <div class="back-home">
                                    <a href="{{ route('home') }}">Back to Home</a>
                                </div>
                            </div>
                            <h1>Sign into Your Account</h1>

                            <form method="POST" action="{{ route('log-adm') }}">
                                @csrf

                                <div class="input-block">
                                    <label class="form-control-label">Email</label>
                                    <input type="email" class="form-control" name="email" :value="old('email')"
                                        required autofocus autocomplete="username" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="input-block">
                                    <label class="form-control-label">Password</label>
                                    <div class="pass-group">
                                        <input type="password" class="form-control pass-input" name="password" required
                                            autocomplete="current-password" />
                                        <span class="feather-eye toggle-password"></span>
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="input-block">
                                    <label class="form-control-label">Code</label>
                                    <div class="pass-group">
                                        <input type="text" class="form-control pass-input" name="google2fa_token"
                                            required autocomplete="google2fa_token" />
                                        <span class="feather-unlock"></span>
                                        <x-input-error :messages="$errors->get('google2fa_token')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="forgot">
                                    @if (Route::has('password.request'))
                                        <span><a class="forgot-link" href="{{ route('password.request') }}">Forgot
                                                Password
                                                ?</a>
                                        </span>
                                        </a>
                                    @endif
                                </div>

                                <div class="remember-me">
                                    <label
                                        class="custom_check d-inline-flex remember-me mb-0 mr-2">{{ __('Remember me') }}
                                        <input id="remember_me" type="checkbox" name="remember">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-primary btn-start" type="submit">Sign In</button>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="google-bg text-center">
                        <span><a href="login.html#">Or sign in with</a></span>
                        <div class="sign-google">
                            <ul>
                                <li><a href="login.html#"><img
                                            src="https://dreamslms.dreamstechnologies.com/html/assets/img/net-icon-01.png"
                                            class="img-fluid" alt="Logo"> Sign
                                        In using Google</a></li>
                                <li><a href="login.html#"><img
                                            src="https://dreamslms.dreamstechnologies.com/html/assets/img/net-icon-02.png"
                                            class="img-fluid" alt="Logo">Sign
                                        In using Facebook</a></li>
                            </ul>
                        </div>
                        <p class="mb-0">New User ? <a href="register.html">Create an Account</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <script src="{{ asset('frontend/assets/js/jquery-3.7.1.min.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/owl.carousel.min.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/script.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($errors->has('google2fa'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ $errors->first('google2fa') }}',
                showConfirmButton: false,
                timer: 3000,
                toast: true
            });
        </script>
    @endif
</body>

</html>
