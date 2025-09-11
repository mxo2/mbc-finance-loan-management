<!DOCTYPE html>
@php
    use App\Models\AuthPage;
    $default_logo = asset(Storage::url('upload/logo/logo.png'));
    $settings = settings();
    $authPage = AuthPage::where('parent_id', 1)->first();
    $titles = $authPage && !empty($authPage->title) ? json_decode($authPage->title, true) : [];
    $descriptions = $authPage && !empty($authPage->description) ? json_decode($authPage->description, true) : [];

@endphp

<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }} - @yield('tab-title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="author" content="{{ !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME') }} - @yield('page-title') </title>

    <meta name="title" content="{{ $settings['meta_seo_title'] }}">
    <meta name="keywords" content="{{ $settings['meta_seo_keyword'] }}">
    <meta name="description" content="{{ $settings['meta_seo_description'] }}">


    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ $settings['meta_seo_title'] }}">
    <meta property="og:description" content="{{ $settings['meta_seo_description'] }}">
    <meta property="og:image" content="{{ !empty($settings['meta_seo_image']) ? fetch_file($settings['meta_seo_image'],'upload/seo') : '#' }}">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ $settings['meta_seo_title'] }}">
    <meta property="twitter:description" content="{{ $settings['meta_seo_description'] }}">
    <meta property="twitter:image"
        content="{{ !empty($settings['meta_seo_image']) ? fetch_file($settings['meta_seo_image'],'upload/logo/') : '#' }}">

    <link rel="icon" href="{{ !empty($settings['company_favicon']) ? fetch_file($settings['company_favicon'],'upload/logo/') : '#' }}" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        id="main-font-link" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />

    @if (!empty($settings['custom_color']) && $settings['color_type'] == 'custom')
        <link rel="stylesheet" id="Pstylesheet" href="{{ asset('assets/css/custom-color.css') }}" />
        <script src="{{ asset('js/theme-pre-color.js') }}"></script>
    @else
        <link rel="stylesheet" id="Pstylesheet" href="{{ asset('assets/css/style-preset.css') }}" />
    @endif


    <link href="{{ asset('css/custom.css') }} " rel="stylesheet">
    <link href="{{ asset('assets/css/blue-theme-override.css') }}" rel="stylesheet">
    <style>
        .top-left-logo {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            max-width: 150px;
        }
        .top-left-logo img {
            max-height: 60px;
            width: auto;
        }
        .auth-wrapper.v2 {
            display: flex;
            min-height: 100vh;
        }
        .auth-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
            padding-top: 100px; /* Add space for top logo */
        }
        @media (max-width: 768px) {
            .top-left-logo {
                top: 10px;
                left: 10px;
                max-width: 120px;
            }
            .top-left-logo img {
                max-height: 50px;
            }
            .auth-form {
                padding-top: 80px;
            }
        }
    </style>
</head>

<body
    data-pc-preset="{{ !empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color'] }}"
    data-pc-sidebar-theme="light" data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}"
    data-pc-direction="{{ $settings['theme_layout'] }}" data-pc-theme="{{ $settings['theme_mode'] }}">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- Top left logo -->
    <div class="top-left-logo">
        <img src="{{ asset('assets/images/logo_mbc.png') }}" alt="MBC Finance Logo" class="img-fluid" />
    </div>
    
    <div class="auth-main">
        <div class="auth-wrapper v2">
            <div class="auth-form">
                @yield('content')
            </div>


            @if (!empty($authPage) && $authPage->section == 1)
                <div class="auth-sidecontent">
                    <div class="p-3 px-lg-5 text-center">
                        <div id="carouselExampleIndicators" class="carousel slide carousel-dark"
                            data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($titles as $index => $title)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <h1><b>{{ $title }}</b></h1>
                                        <p class="f-12 mt-4">{{ $descriptions[$index] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="carousel-indicators position-relative">
                                @foreach ($titles as $index => $title)
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="{{ $index }}"
                                        class="{{ $index == 0 ? 'active' : '' }}"
                                        aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                        aria-label="Slide {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                        </div>
                       <img src="{{ asset($authPage->image) }}" alt="MBC Finance Banner"
                            class="img-fluid mt-3 w-75" />
                    </div>
                </div>
            @endif


        </div>
    </div>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

    @stack('script-page')
    <script>
        font_change('Roboto');
    </script>
</body>

</html>
