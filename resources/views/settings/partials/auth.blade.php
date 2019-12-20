<div class="card">
    <h6 class="card-header">
        @lang('General')
    </h6>

    <div class="card-body">
        {!! Form::open(['route' => 'settings.auth.update', 'id' => 'auth-general-settings-form']) !!}

        <div class="form-group mb-4">
            <div class="d-flex align-items-center">
                 <div class="switch">
                     <input type="hidden" value="0" name="remember_me">
                     {!! Form::checkbox('remember_me', 1, setting('remember_me'), ['class' => 'switch', 'id' => 'switch-remember-me']) !!}
                     <label for="switch-remember-me"></label>
                 </div>
                <div class="ml-3 d-flex flex-column">
                    <label class="mb-0">@lang('Allow "Remember Me"')</label>
                    <small class="pt-0 text-muted">
                        @lang("Should 'Remember Me' checkbox be displayed on login form?")
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group my-4">
            <div class="d-flex align-items-center">
                <div class="switch">
                    <input type="hidden" value="0" name="forgot_password">
                    {!! Form::checkbox('forgot_password', 1, setting('forgot_password'), ['class' => 'switch', 'id' => 'switch-forgot-pass']) !!}
                    <label for="switch-forgot-pass"></label>
                </div>
                <div class="ml-3 d-flex flex-column">
                    <label class="mb-0">@lang('Forgot Password')</label>
                    <small class="pt-0 text-muted">
                        @lang('Enable/Disable forgot password feature.')
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group my-4">
            <label for="login_reset_token_lifetime">
                @lang('Reset Token Lifetime') <br>
                <small class="text-muted">
                    @lang('Number of minutes that the reset token should be considered valid.')
                </small>
            </label>
            <input type="text" name="login_reset_token_lifetime"
                   class="form-control input-solid" value="{{ setting('login_reset_token_lifetime', 30) }}">
        </div>

        <button type="submit" class="btn btn-primary">
            @lang('Update')
        </button>

        {!! Form::close() !!}
    </div>
</div>
