@extends('layouts.auth')

@section('page-title', __('Reset Password'))

@section('content')

<div class="col-md-8 col-lg-6 col-xl-5 mx-auto my-10p">
    <div class="text-center">
        <img src="{{ url('assets/img/sm-logo.png') }}" alt="{{ setting('app_name') }}" height="50">
    </div>

    <div class="card mt-5">
        <div class="card-body">
            <h5 class="card-title text-center mt-4 mb-2 text-uppercase">
                @lang('Forgot Your Password?')
            </h5>

            <div class="p-4">
                <form role="form" action="<?= route('password.email') ?>" method="POST" id="remind-password-form" autocomplete="off">
                    {{ csrf_field() }}

                    <p class="text-muted mb-4 text-center font-weight-light">
                        @lang('Please provide your email below and we will send you a password reset link.')
                    </p>

                    @include('partials.messages')

                    <div class="form-group password-field my-3">
                        <label for="password" class="sr-only">@lang('Email')</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control input-solid"
                               placeholder="@lang('Your E-Mail')">
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-reset-password">
                            @lang('Reset Password')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@stop

@section('scripts')
    {!! JsValidator::formRequest('Vanguard\Http\Requests\Auth\PasswordRemindRequest', '#remind-password-form') !!}
@stop
