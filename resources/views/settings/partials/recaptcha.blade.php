<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-1">
            @lang('Google reCAPTCHA')
        </h5>

        <small class="text-muted d-block mb-4">
            @lang('Enable/Disable Google reCAPTCHA during the registration.')
        </small>

        @if (! (config('captcha.secret') && config('captcha.sitekey')))
            <div class="alert alert-info">
                @lang('To utilize Google reCAPTCHA, please get your') <code>@lang('Site Key')</code>
                @lang('and') <code>@lang('Secret Key')</code>
                @lang('from')
                <a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">
                    <strong>@lang('reCAPTCHA Website')</strong>
                </a>,
                @lang('and update your') <code>RECAPTCHA_SITEKEY</code> @lang('and')
                <code>RECAPTCHA_SECRETKEY</code> @lang('environment variables inside') <code>.env</code>
                @lang('file').
            </div>
        @else
            @if (setting('registration.captcha.enabled'))
                {!! Form::open(['route' => 'settings.registration.captcha.disable', 'id' => 'captcha-settings-form']) !!}
                <button type="submit" class="btn btn-danger">
                    @lang('Disable')
                </button>
                {!! Form::close() !!}
            @else
                {!! Form::open(['route' => 'settings.registration.captcha.enable', 'id' => 'captcha-settings-form']) !!}
                <button type="submit" class="btn btn-primary">
                    @lang('Enable')
                </button>
                {!! Form::close() !!}
            @endif
        @endif
    </div>
</div>
