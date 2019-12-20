@extends('layouts.auth')

@section('page-title', trans('Login'))

@section('content')
<!-- <div class="col-md-8 col-lg-6 col-xl-5 mx-auto my-10p" id="login">
    <div class="text-center">
        <img src="{{ url('assets/img/sm-logo.png') }}" alt="{{ setting('app_name') }}" height="50">
    </div> -->

<div class="col-md-8 col-lg-6 col-xl-5 mx-auto my-10p" id="login">
    <div class="card mt-5">
        <div class="card-body">
            <h5 class="card-title text-center mt-4 text-uppercase">
                <img src="{{ url('assets/img/sm-logo.png') }}" alt="{{ setting('app_name') }}" height="50">
            </h5>

            <div class="p-4">
                @include('auth.social.buttons')

                @include('partials.messages')

                <form role="form" action="<?= url('login') ?>" method="POST" id="login-form" autocomplete="off" class="mt-3">

                    <input type="hidden" value="<?= csrf_token() ?>" name="_token">

                    @if (Request::has('to'))
                        <input type="hidden" value="{{ Input::get('to') }}" name="to">
                    @endif

                    <div class="form-group input-icon">
                        <label for="username" class="sr-only">@lang('Email or Username')</label>
                        <i class="fa fa-user"></i>
                        <input type="text"
                                name="username"
                                id="username"
                                class="form-control input-solid"
                                placeholder="@lang('Email or Username')"
                                value="{{ old('username') }}">
                    </div>

                    <div class="form-group password-field input-icon">
                        <label for="password" class="sr-only">@lang('Password')</label>
                        <i class="fa fa-lock"></i>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control input-solid"
                               placeholder="@lang('Password')">
                    </div>


                    @if (setting('remember_me'))
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="remember" id="remember" value="1"/>
                            <label class="custom-control-label font-weight-normal" for="remember">
                                @lang('Remember me?')
                            </label>
                        </div>
                    @endif


                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-login">
                            @lang('Log In')
                        </button>
                    </div>
                </form>

                @if (setting('forgot_password'))
                    <a href="<?= route('password.request') ?>" class="forgot">@lang('I forgot my password')</a>
                @endif
            </div>
        </div>
    </div>

    <div class="text-center text-muted">
        @if (setting('reg_enabled'))
            @lang("Don't have an account?")
            <a class="font-weight-bold" href="<?= url("register") ?>">@lang('Sign Up')</a>
        @endif
    </div>
</div>

@stop

@section('scripts')
    {!! HTML::script('assets/js/as/login.js') !!}
    {!! JsValidator::formRequest('Vanguard\Http\Requests\Auth\LoginRequest', '#login-form') !!}
@stop
