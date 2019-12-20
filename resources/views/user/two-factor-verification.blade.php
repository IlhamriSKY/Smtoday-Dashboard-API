@extends('layouts.app')

@section('page-title', __('My Profile'))
@section('page-heading', __('My Profile'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        @lang('My Profile')
    </li>

    <li class="breadcrumb-item active">
        @lang('Two-Factor Phone Verification')
    </li>
@stop

@section('content')

@include('partials.messages')

<div class="row">
    <div class="col-md-6 m-auto">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title card-title-bold">
                    @lang('Phone Verification')
                </h5>

                <p>
                    @lang('We have sent you a verification token via SMS. Please provide the token
                    below to verify your phone number.')
                </p>

                {!! Form::open(['route' => "two-factor.verify", 'id' => 'two-factor-form']) !!}
                    @if ($user)
                        <input type="hidden" name="user" value="{{ $user }}">
                    @endif
                    <div class="form-group mt-4">
                        <input type="text"
                               class="form-control"
                               id="token"
                               name="token"
                               placeholder="@lang('Token')">
                    </div>
                    <div class="mt-3">
                        <button type="submit"
                                class="btn btn-primary"
                                data-toggle="loader"
                                data-loading-text="@lang('Verifying...')">
                            @lang('Verify')
                        </button>
                        <a href="javascript:;"
                           class="btn d-none"
                           id="resend-token"
                           data-loading-text="@lang('Sending...')">
                            @lang('Resend Token')
                        </a>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@stop

@section('scripts')
    <script>
        var user = {{ isset($user) ? $user : 'null' }};
    </script>
    {!! HTML::script('assets/js/as/btn.js') !!}
    {!! HTML::script('assets/js/as/two-factor.js') !!}
@stop