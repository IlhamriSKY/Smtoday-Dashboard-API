<div class="card">
    <h6 class="card-header">
        @lang('Authentication Throttling')
    </h6>

    <div class="card-body">
        {!! Form::open(['route' => 'settings.auth.update', 'id' => 'auth-throttle-settings-form']) !!}

        <div class="form-group mb-4">
            <div class="d-flex align-items-center">
                <div class="switch">
                    <input type="hidden" value="0" name="throttle_enabled">
                    {!! Form::checkbox('throttle_enabled', 1, setting('throttle_enabled'), ['class' => 'switch', 'id' => 'switch-throttle']) !!}
                    <label for="switch-throttle"></label>
                </div>
                <div class="ml-3 d-flex flex-column">
                    <label class="mb-0">@lang('Throttle Authentication')</label>
                    <small class="text-muted">
                        @lang('Should the system throttle authentication attempts?')
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group my-4">
            <label for="throttle_attempts">
                @lang('Maximum Number of Attempts') <br>
                <small class="text-muted">
                    @lang('Maximum number of incorrect login attempts before lockout.')
                </small>
            </label>
            <input type="text" name="throttle_attempts" class="form-control input-solid"
                   value="{{ setting('throttle_attempts', 10) }}">
        </div>

        <div class="form-group my-4">
            <label for="throttle_lockout_time">
                @lang('Lockout Time') <br>
                <small class="text-muted">
                    @lang('Number of minutes to lock the user out for after specified maximum number of incorrect login attempts.')
                </small>
            </label>

            <input type="text" name="throttle_lockout_time" class="form-control input-solid"
                   value="{{ setting('throttle_lockout_time', 1) }}">
        </div>

        <button type="submit" class="btn btn-primary">
            @lang('Update')
        </button>

        {!! Form::close() !!}
    </div>
</div>
