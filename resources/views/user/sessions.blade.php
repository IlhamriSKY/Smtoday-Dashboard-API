@extends('layouts.app')

@section('page-title', $user->present()->nameOrEmail . ' - ' . __('Active Sessions'))

@section('page-heading')
    @lang('Sessions') ({{ $user->present()->nameOrEmail }})
@stop

@section('breadcrumbs')
    @if (isset($adminView))
        <li class="breadcrumb-item">
            <a href="{{ route('users.index') }}">@lang('Users')</a>
        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('users.show', $user->id) }}">
                {{ $user->present()->nameOrEmail }}
            </a>
        </li>
    @endif

    <li class="breadcrumb-item active">
        @lang('Sessions')
    </li>
@stop

@section('content')

@include('partials.messages')

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-borderless table-striped">
                <thead>
                    <tr>
                        <th>@lang('IP Address')</th>
                        <th>@lang('Device')</th>
                        <th>@lang('Browser')</th>
                        <th>@lang('Last Activity')</th>
                        <th class="text-center">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($sessions))
                        @foreach ($sessions as $session)
                            <tr>
                                <td>{{ $session->ip_address }}</td>
                                <td>
                                    {{ $session->device ?: __('Unknown') }} ({{ $session->platform ?: __('Unknown') }})
                                </td>
                                <td>{{ $session->browser ?: __('Unknown') }}</td>
                                <td>{{ $session->last_activity->format(config('app.date_time_format')) }}</td>
                                <td class="text-center">
                                    <a href="{{ isset($profile) ? route('profile.sessions.invalidate', $session->id) : route('user.sessions.invalidate', [$user->id, $session->id]) }}"
                                       class="btn btn-icon"
                                       title="@lang('Invalidate Session')"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       data-method="DELETE"
                                       data-confirm-title="@lang('Please Confirm')"
                                       data-confirm-text="@lang('Are you sure that you want to invalidate this session?')"
                                       data-confirm-delete="@lang('Yes, proceed!')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6"><em>@lang('No records found.')</em></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop
