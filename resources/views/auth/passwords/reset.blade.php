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
                @lang('Reset Your Password')
            </h5>

            <form role="form" action="{{ route('password.update') }}" method="POST" id="reset-password-form"
                  autocomplete="off" class="p-4">
                <input type="hidden" name="token" value="{{ $token }}">
                {{ csrf_field() }}

                <p class="text-muted mb-4 text-center font-weight-light px-2">
                    @lang('Please provide your email and pick a new password below.')
                </p>

                @include('partials.messages')

                <div class="form-group">
                    <label for="password" class="sr-only">@lang('Your E-Mail')</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control input-solid"
                           placeholder="@lang('Your E-Mail')">
                </div>

                <div class="form-group">
                    <label for="password" class="sr-only">@lang('New Password')</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control input-solid"
                           placeholder="@lang('New Password')">
                </div>

                <div class="form-group">
                    <label for="password" class="sr-only">@lang('Confirm New Password')</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="form-control input-solid"
                           placeholder="@lang('Confirm New Password')">
                </div>

                <div class="form-group mt-5">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-reset-password">
                        @lang('Update Password')
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@stop

@section('scripts')
    {!! JsValidator::formRequest('Vanguard\Http\Requests\Auth\PasswordResetRequest', '#reset-password-form') !!}
@stop
