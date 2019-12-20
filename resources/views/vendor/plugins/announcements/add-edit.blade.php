@extends('layouts.app')

@section('page-title', $edit ? __('Update Announcement') : __('New Announcement'))
@section('page-heading', $edit ? __('Update Announcement') : __('New Announcement'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('announcements.index') }}">@lang('Announcements')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ $edit ? __('Update') : __('Create') }}
    </li>
@stop

@section('content')

    @include('partials.messages')

    @if ($edit)
        {!! Form::open(['route' => ['announcements.update', $announcement], 'id' => 'announcement-form', 'method' => 'PUT']) !!}
    @else
        {!! Form::open(['route' => 'announcements.store', 'id' => 'announcement-form']) !!}
    @endif
        <div class="row">
            <div class="col-md-6 my-4 mx-auto">
                <div class="card">
                    <h6 class="card-header">
                        {{ $edit ? __('Update Announcement') : __('Create an Announcement') }}
                    </h6>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">@lang('Title')</label>
                            <input type="text"
                                   class="form-control input-solid"
                                   id="title"
                                   name="title"
                                   placeholder="@lang('What are you announcing?')"
                                   value="{{ $edit ? $announcement->title : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="body">@lang('Body')</label>
                            <textarea
                                name="body"
                                class="form-control input-solid"
                                rows="10"
                                id="body"
                                placeholder="@lang('Describe your announcement using markdown...')"
                            >{{ $edit ? $announcement->body : '' }}</textarea>
                        </div>

                        @if (! $edit)
                            <div class="form-group mt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           name="email_notification"
                                           id="email_notification"
                                           value="1"/>

                                    <label class="custom-control-label font-weight-normal" for="email_notification">
                                        <span class="d-block">@lang('E-Mail Notification')</span>
                                        <small>@lang('Send email notification about the announcement to all users.')</small>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            {{ $edit ? __('Update Announcement') : __('Create Announcement') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('scripts')
    {!! JsValidator::formRequest(\Vanguard\Announcements\Http\Requests\AnnouncementRequest::class, '#announcement-form') !!}
@stop
