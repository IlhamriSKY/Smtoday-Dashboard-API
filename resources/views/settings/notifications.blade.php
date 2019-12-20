@extends('layouts.app')

@section('page-title', __('Notification Settings'))
@section('page-heading', __('Notification Settings'))

@section('breadcrumbs')
    <li class="breadcrumb-item text-muted">
        @lang('Settings')
    </li>
    <li class="breadcrumb-item active">
        @lang('Notifications')
    </li>
@stop

@section('content')

@include('partials.messages')

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <h5 class="card-header">
                @lang('Email Notifications')
            </h5>

            <div class="card-body">
                {!! Form::open(['route' => 'settings.notifications.update', 'id' => 'notification-settings-form']) !!}

                    <div class="form-group mb-4">
                        <div class="d-flex align-items-center">
                            <div class="switch">
                                <input type="hidden" value="0" name="notifications_signup_email">

                                <input type="checkbox"
                                       name="notifications_signup_email"
                                       class="switch"
                                       value="1"
                                       id="switch-signup-email"
                                       {{ setting('notifications_signup_email') ? 'checked' : '' }}>

                                <label for="switch-signup-email"></label>
                            </div>
                            <div class="ml-3 d-flex flex-column">
                                <label class="mb-0">@lang('Sign-Up Notification')</label>

                                <small class="pt-0 text-muted">
                                    @lang('Send an email to the Administrators when user signs up.')
                                </small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        @lang('Update')
                    </button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@stop
