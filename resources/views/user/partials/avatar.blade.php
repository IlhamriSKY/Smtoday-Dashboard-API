<div class="avatar-wrapper">
    <div class="spinner">
        <div class="spinner-dot"></div>
        <div class="spinner-dot"></div>
        <div class="spinner-dot"></div>
    </div>
    <div id="avatar"></div>
    <div class="text-center">
        <div class="avatar-preview">
            <img class="avatar rounded-circle img-thumbnail img-responsive mt-5 mb-4"
                 width="150"
                 src="{{ $edit ? $user->present()->avatar : url('assets/img/profile.png') }}">

            <h5 class="text-muted">{{ $user->present()->nameOrEmail }}</h5>
        </div>

        <div id="change-picture"
             class="btn btn-outline-secondary btn-block mt-5"
             data-toggle="modal"
             data-target="#choose-modal">
            <i class="fa fa-camera"></i>
            @lang('Change Photo')
        </div>

        <div class="row avatar-controls d-none">
            <div class="col-md-6">
                <div id="cancel-upload" class="btn btn-block btn-outline-secondary text-center">
                    <i class="fa fa-times"></i> @lang('Cancel')
                </div>
            </div>
            <div class="col-md-6">
                <button type="submit" id="save-photo" class="btn btn-success btn-block text-center">
                    <i class="fa fa-check"></i> @lang('Save')
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="choose-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 avatar-source" id="no-photo"
                         data-url="{{ $updateUrl }}">
                        <img src="{{ url('assets/img/profile.png') }}" class="rounded-circle img-thumbnail img-responsive">
                        <p class="mt-3">@lang('No Photo')</p>
                    </div>
                    <div class="col-md-4 avatar-source">
                        <div class="btn btn-light btn-upload">
                            <i class="fa fa-upload"></i>
                            <input type="file" name="avatar" id="avatar-upload">
                        </div>
                        <p class="mt-3">@lang('Upload Photo')</p>
                    </div>
                    @if ($edit)
                        <div class="col-md-4 avatar-source source-external"
                             data-url="{{ $updateUrl }}">
                            <img src="{{ $user->gravatar() }}" class="rounded-circle img-thumbnail img-responsive">
                            <p class="mt-3">@lang('Gravatar')</p>
                        </div>
                    @endif
                </div>

                @if ($edit && count($socialLogins))
                    @foreach ($socialLogins->chunk(3) as $logins)
                        <br>
                        <div class="row">
                            @foreach($logins as $login)
                                <div class="col-md-4 avatar-source source-external"
                                     data-url="{{ $updateUrl }}">
                                    <img src="{{ $login->avatar }}"
                                         class="rounded-circle img-thumbnail img-responsive"
                                         style="width: 120px;">
                                    <p>{{ ucfirst($login->provider) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-none">
    <input type="hidden" name="points[x1]" id="points_x1">
    <input type="hidden" name="points[y1]" id="points_y1">
    <input type="hidden" name="points[x2]" id="points_x2">
    <input type="hidden" name="points[y2]" id="points_y2">
</div>
