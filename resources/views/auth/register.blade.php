@extends('layouts.auth')

@section('page-title', __('Sign Up'))

@if (setting('registration.captcha.enabled'))
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif

@section('content')

    <div class="col-md-8 col-lg-6 col-xl-5 mx-auto my-10p">
        <div class="text-center">
            <img src="{{ url('assets/img/sm-logo.png') }}" alt="{{ setting('app_name') }}" height="50">
        </div>

        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title text-center mt-4 text-uppercase">
                    @lang('Register')
                </h5>

                <div class="p-4">
                    @include('auth.social.buttons')

                    @include('partials/messages')

                    <form role="form" action="<?= url('register') ?>" method="post" id="registration-form" autocomplete="off" class="mt-3">
                        <input type="hidden" value="<?= csrf_token() ?>" name="_token">
                        <div class="form-group">
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control input-solid"
                                   placeholder="@lang('Email')"
                                   value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <input type="text"
                                   name="username"
                                   id="username"
                                   class="form-control input-solid"
                                   placeholder="@lang('Username')"
                                   value="{{ old('username') }}">
                        </div>
                        <div class="form-group">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control input-solid"
                                   placeholder="@lang('Password')">
                        </div>
                         <div class="form-group">
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control input-solid"
                                   placeholder="@lang('Confirm Password')">
                        </div>

                        @if (setting('tos'))
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="tos" id="tos" value="1"/>
                                <label class="custom-control-label font-weight-normal" for="tos">
                                    @lang('I accept')
                                    <a href="#tos-modal" data-toggle="modal">@lang('Terms of Service')</a>
                                </label>
                            </div>
                        @endif

                        {{-- Only display captcha if it is enabled --}}
                        @if (setting('registration.captcha.enabled'))
                            <div class="form-group my-4">
                                {!! app('captcha')->display() !!}
                            </div>
                        @endif
                        {{-- end captcha --}}

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-login">
                                @lang('Register')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="text-center text-muted">
            @if (setting('reg_enabled'))
                @lang('Already have an account?')
                <a class="font-weight-bold" href="<?= url("login") ?>">@lang('Login')</a>
            @endif
        </div>

    </div>

    @if (setting('tos'))
        <div class="modal fade" id="tos-modal" tabindex="-1" role="dialog" aria-labelledby="tos-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tos-label">@lang('Terms of Service')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @include('auth.tos')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            @lang('Close')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@stop

@section('scripts')
    {!! JsValidator::formRequest('Vanguard\Http\Requests\Auth\RegisterRequest', '#registration-form') !!}
@stop
