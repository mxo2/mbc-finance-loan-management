@extends('layouts.auth')
@php
    $settings = settings();
@endphp
@section('tab-title')
    {{ __('Login') }}
@endsection
@push('script-page')
    @if ($settings['google_recaptcha'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
@section('content')
    @php
        $registerPage = getSettingsValByName('register_page');
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <div class="auth-header">
                        <h2 class="text-primary"><b>{{ __('Welcome to MBC Finance') }} </b></h2>
                        <p class="f-16 mt-2 text-primary">{{ __('Your trusted partner for instant consumer loans') }}</p>
                    </div>
                </div>
            </div>

            {{ Form::open(['route' => 'custom.login', 'method' => 'post', 'id' => 'loginForm', 'class' => 'login-form']) }}
            <input type="hidden" name="source" id="source-input" value="{{ request()->is('pwa/*') ? 'pwa' : 'web' }}">
            <script>
                // Check if the URL contains '/pwa/' to identify PWA requests
                if (window.location.href.indexOf('/pwa/') !== -1) {
                    document.getElementById('source-input').value = 'pwa';
                }
                // Also check the referrer URL
                if (document.referrer.indexOf('/pwa/') !== -1) {
                    document.getElementById('source-input').value = 'pwa';
                }
            </script>
            @if (session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email"
                    placeholder="{{ __('Email address') }}" />
                <label for="email">{{ __('Email address') }}</label>
                @error('email')
                    <span class="invalid-email text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="{{ __('Password') }}" />
                <label for="password">{{ __('Password') }}</label>
                @error('password')
                    <span class="invalid-password text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="d-flex mt-1 justify-content-between">
                <div class="form-check">
                    <input class="form-check-input input-primary" type="checkbox" id="agree"
                        {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label text-muted" for="agree">{{ __('Remember me') }}</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-secondary">{{ __('Forgot Password?') }}</a>
                @endif
            </div>
            @if ($settings['google_recaptcha'] == 'on')
                <div class="form-group">
                    <label for="email" class="form-label"></label>
                    {!! NoCaptcha::display() !!}
                    @error('g-recaptcha-response')
                        <span class="small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                @if ($errors->has('g-recaptcha-response'))
                    <span class="help-block">
                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                    </span>
                @endif
            @endif
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary p-2">{{ __('Sign In to MBC Finance') }}</button>
            </div>
            @if ($registerPage == 'on')
                <hr />
                <h5 class="d-flex justify-content-center text-muted">{{ __("New to MBC Finance?") }} <a class="ms-1 text-primary"
                        href="{{ route('register') }}">{{ __('Apply for Loan Account') }}</a>
                </h5>
            @endif
            {{ Form::close() }}
        </div>
    </div>
@endsection
