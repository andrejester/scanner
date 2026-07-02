<!--
 Author: W3layouts
 Author URL: http://w3layouts.com
 License: Creative Commons Attribution 3.0 Unported
 License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE html>
<html lang="en">
<!-- Head -->

<head>
    <title>{{ env('APP_NAME') }}</title>
    <!-- Meta-Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
    <!-- //Meta-Tags -->

    <!-- Custom-Style-Sheet -->
    <link rel="stylesheet" href="{{ asset('assets/login/css/popuo-box.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/login/css/style.css') }}" type="text/css" media="all">
    <link rel="stylesheet" href="{{ asset('assets/login/css/font-awesome.css') }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Montserrat:400,700" type="text/css" media="all">
    <!-- //Fonts -->

</head>
<!-- //Head -->

<!-- Body -->

<body>

    <h1>{{ env('APP_NAME') }}</h1>

    <div class="containerw3layouts-agileits">

        <div class="w3imageaits">
            <div class="header-social wthree">
                <ul>
                    <li><a href="#" class="f"><i class="fa fa-facebook" aria-hidden="true"></i>Login with
                            Facebook</a></li>
                    <li><a href="#" class="t"><i class="fa fa-twitter" aria-hidden="true"></i>Login with
                            Twitter</a></li>
                    <li><a href="#" class="g"><i class="fa fa-google-plus" aria-hidden="true"></i>Login with
                            Google+</a></li>
                </ul>
            </div>
        </div>

        <div class="aitsloginwthree w3layouts agileits">
            <h2>Login With Email</h2>
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <form method="POST" action="{{ route('log-adm') }}">
                @csrf
                <div class="mail">
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    <x-text-input placeholder="Email" id="email" type="email" name="email" :value="old('email')"
                        required autofocus autocomplete="username" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />

                <div class="password">
                    <i class="fa fa-unlock" aria-hidden="true"></i>
                    <x-text-input id="password" placeholder="Password" type="password" name="password" required
                        autocomplete="current-password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                <div class="password">
                    <i class="fa fa-unlock" aria-hidden="true"></i>
                    <x-text-input type="text" name="google2fa_token" placeholder="Code" required />
                </div>

                <div class="send-button wthree agileits">
                    <input type="submit" value="{{ __('Log in') }}">
                </div>


                <ul class="tick w3layouts agileinfo">
                    <li class="label1">
                        @if (Route::has('password.request'))
                            <a class="for" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </li>
                </ul>
            </form>
        </div>

        <div class="clear"></div>

    </div>


    <!-- for register popup -->
    <div id="small-dialog1" class="mfp-hide">
        <div class="contact-form1">
            <div class="contact-w3-agileits w3layouts">
                <h3>Signup Here</h3>
                <form action="#" method="post">
                    <div class="user">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <input type="text" Name="Userame" placeholder="First Name" required="">
                    </div>
                    <div class="user">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <input type="text" Name="Userame" placeholder="Last Name" required="">
                    </div>
                    <div class="mail">
                        <i class="fa fa-envelope" aria-hidden="true"></i>
                        <input type="email" Name="Userame" placeholder="Email" required="">
                    </div>
                    <div class="password psw1">
                        <i class="fa fa-unlock" aria-hidden="true"></i>
                        <input type="password" Name="Password" placeholder="Password" required="">
                    </div>
                    <div class="password">
                        <i class="fa fa-unlock" aria-hidden="true"></i>
                        <input type="password" Name="Password" placeholder="Confirm Password" required="">
                    </div>
                    <div class="login-check">
                        <label class="checkbox"><input type="checkbox" name="checkbox" checked="">I Accept Terms
                            &amp; Conditions</label>
                    </div>
                    <div class="submit-w3l">
                        <input type="submit" value="Sign up">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- //for register popup -->

    <div class="w3lsfooteragileits">
        <p>&copy;All rights reserved.<br>{{ date('Y') }} <a href="https://pentamediatraining.com/"
                target="_blank">
                pentamediatraining</a></p>
    </div>
    <link rel="stylesheet" href="{{ asset('assets/login/js/jquery-2.1.4.min.js') }}">
    <!-- pop-up-box-js-file -->
    <link rel="stylesheet" href="{{ asset('assets/login/js/jquery.magnific-popup.js') }}">
    <!--//pop-up-box-js-file -->
    <script>
        $(document).ready(function() {
            $('.w3_play_icon,.w3_play_icon1,.w3_play_icon2').magnificPopup({
                type: 'inline',
                fixedContentPos: false,
                fixedBgPos: true,
                overflowY: 'auto',
                closeBtnInside: true,
                preloader: false,
                midClick: true,
                removalDelay: 300,
                mainClass: 'my-mfp-zoom-in'
            });

        });
    </script>

</body>
<!-- //Body -->

</html>
