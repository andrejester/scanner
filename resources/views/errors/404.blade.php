<!doctype html>
<html class="no-js" lang="zxx" dir="ltr">

<head>
    {{-- BASIC --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name'))</title>
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <meta name="description" content="@yield('meta_description', 'Pascasarjana Unmer')">
    <meta name="keywords" content="@yield('meta_keywords', 'Pascasarjana Unmer, S2 Unmer, S3 Unmer')">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Pascasarjana Unmer">

    {{-- FAVICON (clean & cukup) --}}
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('frontend/assets/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('frontend/assets/img/favicons/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('frontend/assets/img/favicons/apple-icon-180x180.png') }}">
    <link rel="manifest" href="{{ asset('frontend/assets/img/favicons/manifest.json') }}">

    <meta name="theme-color" content="#ffffff">

    {{-- OPEN GRAPH (Facebook, WhatsApp, dll) --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', 'Pascasarjana Unmer')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:image" content="@yield('meta_image', asset('frontend/assets/img/logo/favicon.png'))">

    {{-- TWITTER --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('app.name'))">
    <meta name="twitter:description" content="@yield('meta_description', 'Pascasarjana Unmer')">
    <meta name="twitter:image" content="@yield('meta_image', asset('frontend/assets/img/logo/favicon.png'))">

    {{-- OPTIONAL ARTICLE META --}}
    <meta property="article:published_time" content="@yield('og.article.published_time')">
    <meta property="article:modified_time" content="@yield('og.article.modified_time')">
    <meta property="article:author" content="@yield('og.article.author')">

    <meta name="theme-color" content="#ffffff">

    {{-- PRELOAD (opsional untuk performa)
    <link rel="preload" href="{{ asset('frontend/assets/css/app.css') }}" as="style">
    <link rel="preload" href="{{ asset('frontend/assets/js/app.js') }}" as="script"> --}}

    {{-- JSON-LD (SEO Structured Data) --}}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "{{ config('app.name') }}",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('frontend/assets/img/logo/favicon.png') }}"
        }
    </script>


    @include('frontend.includes.main-css')
    @stack('meta')
</head>

<body>
    <section class="space">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="error-page">
                        <div class="error-img"><img src="{{ asset('frontend/assets/img/normal/error.png') }}"
                                alt="404 image"></div>
                        <div class="error-content">
                            <h5 class="">uh-oh! Nothing here...</h5>
                            <h2 class="page-title mt-n2">The page doesn’t exit</h2>
                            <p class="error-text mb-35">Oops! The page you're looking for does not exist.
                            </p><a href="https://alumnipascaunmer.id/" class="th-btn">Back To Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('frontend.includes.main-js')
</body>

</html>
