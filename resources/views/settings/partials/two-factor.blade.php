<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-1">@lang('Two-Factor Authentication')</h5>

        <small class="text-muted d-block mb-4">
            @lang('Enable/Disable Two-Factor Authentication for the application.')
        </small>

        @if (! config('services.authy.key'))
            <div class="alert alert-info">
                @lang('In order to enable Two-Factor Authentication you have to register and create new application on')
                <a href="https://www.authy.com/" target="_blank"><strong>@lang('Authy website')</strong></a>,
                @lang('and update your') <code>AUTHY_KEY</code>
                @lang('environment variable inside') <code>.env</code> @lang('file').
            </div>
        @else
            @if (setting('2fa.enabled'))
                {!! Form::open(['route' => 'settings.auth.2fa.disable', 'id' => 'auth-2fa-settings-form']) !!}
                <button type="submit"
                        class="btn btn-danger"
                        data-toggle="loader"
                        data-loading-text="@lang('Disabling...')">
                    @lang('Disable')
                </button>
                {!! Form::close() !!}
            @else
                {!! Form::open(['route' => 'settings.auth.2fa.enable', 'id' => 'auth-2fa-settings-form']) !!}
                <button type="submit"
                        class="btn btn-primary"
                        data-toggle="loader"
                        data-loading-text="@lang('Enabling...')">
                    @lang('Enable')
                </button>
                {!! Form::close() !!}
            @endif
        @endif
    </div>
</div>
