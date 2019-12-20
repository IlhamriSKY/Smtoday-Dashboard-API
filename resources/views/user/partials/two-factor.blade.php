@if (! Authy::isEnabled($user))
    <div class="alert alert-info">
        @lang('In order to enable Two-Factor Authentication, you must install')
        <a target="_blank" href="https://www.authy.com/">Authy</a>
        @lang('application on your phone').
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="country_code">@lang('Country Code')</label>
                <input type="text"
                       class="form-control"
                       id="country_code"
                       placeholder="381"
                       name="country_code"
                       value="{{ $user->two_factor_country_code }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone_number">@lang('Cell Phone')</label>
                <input type="text"
                       class="form-control"
                       id="phone_number"
                       placeholder="@lang('Phone without country code')"
                       name="phone_number"
                       value="{{ $user->two_factor_phone }}">
            </div>
        </div>
    </div>

    <button type="submit"
            class="btn btn-primary"
            data-toggle="loader"
            data-loading-text="@lang('Enabling...')">
        @lang('Enable')
    </button>
@else
    <button type="submit"
            class="btn btn-danger mt-2"
            data-toggle="loader"
            data-loading-text="@lang('Disabling...')">
        <i class="fa fa-close"></i>
        @lang('Disable')
    </button>
@endif
