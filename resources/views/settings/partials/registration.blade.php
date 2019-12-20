<div class="card">
    <h6 class="card-header">@lang('General')</h6>

    <div class="card-body">
        {!! Form::open(['route' => 'settings.auth.update', 'id' => 'registration-settings-form']) !!}

        <div class="form-group mb-4">
            <div class="d-flex align-items-center">
                <div class="switch">
                    <input type="hidden" value="0" name="reg_enabled">

                    <input
                        type="checkbox" name="reg_enabled"
                        id="switch-reg-enabled"
                        class="switch" value="1"
                        {{ setting('reg_enabled') ? 'checked' : '' }}>

                    <label for="switch-reg-enabled"></label>
                </div>
                <div class="ml-3 d-flex flex-column">
                    <label class="mb-0">@lang('Allow Registration')</label>
                </div>
            </div>
        </div>

        <div class="form-group my-4">
            <div class="d-flex align-items-center">
                <div class="switch">
                    <input type="hidden" value="0" name="tos">
                    {!! Form::checkbox('tos', 1, setting('tos'), ['class' => 'switch', 'id' => 'switch-tos']) !!}
                    <label for="switch-tos"></label>
                </div>
                <div class="ml-3 d-flex flex-column">
                    <label class="mb-0">@lang('Terms & Conditions')</label>
                    <small class="pt-0 text-muted">
                        @lang('The user has to confirm that he agree with terms and conditions in order to create an account.')
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group my-4">
            <div class="d-flex align-items-center">
                <div class="switch">
                    <input type="hidden" value="0" name="reg_email_confirmation">
                    {!! Form::checkbox('reg_email_confirmation', 1, setting('reg_email_confirmation'), ['class' => 'switch', 'id' => 'switch-reg-email-confirm']) !!}
                    <label for="switch-reg-email-confirm"></label>
                </div>
                <div class="ml-3 d-flex flex-column">
                    <label class="mb-0">
                        @lang('Email Confirmation')
                    </label>
                    <small class="text-muted">
                        @lang('Require email confirmation from your newly registered users.')
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
