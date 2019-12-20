@extends('layouts.app')

@section('page-title', __('Announcements'))
@section('page-heading', __('Announcements'))

@section('breadcrumbs')
    <li class="breadcrumb-item active">
        @lang('Announcements')
    </li>
@stop

@section('content')

    @include('partials.messages')

    <div class="d-flex mb-4">
        <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-rounded ml-auto">
            <i class="fas fa-plus mr-2"></i>
            @lang('Create Announcement')
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-borderless table-striped">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="min-width-150">@lang('Creator')</th>
                        <th class="min-width-150">@lang('Title')</th>
                        <th class="min-width-150">@lang('Created At')</th>
                        <th class="text-center min-width-150">@lang('Action')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (count($announcements))
                        @foreach ($announcements as $announcement)
                            <tr>
                                <td style="width: 40px;">
                                    <a href="{{ route('users.show', $announcement->creator) }}">
                                        <img
                                            class="rounded-circle img-responsive"
                                            width="40"
                                            src="{{ $announcement->creator->present()->avatar }}"
                                            alt="{{ $announcement->creator->present()->name }}">
                                    </a>
                                </td>
                                <td class="align-middle">
                                    @permission('users.manage')
                                        <a href="{{ route('users.show', $announcement->creator) }}">
                                            {{ $announcement->creator->username ?: __('N/A') }}
                                        </a>
                                    @else
                                        <span>{{ $announcement->creator->username ?: __('N/A') }}</span>
                                    @endpermission
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('announcements.show', $announcement) }}">
                                        {{ \Illuminate\Support\Str::limit($announcement->title, 50) }}
                                    </a>
                                </td>
                                <td class="align-middle">
                                    {{ $announcement->created_at->format(config('app.date_format')) }}
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('announcements.edit', $announcement) }}"
                                       class="btn btn-icon edit"
                                       title="@lang('Edit Announcement')"
                                       data-toggle="tooltip" data-placement="top">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="{{ route('announcements.destroy', $announcement) }}"
                                       class="btn btn-icon"
                                       title="@lang('Delete Announcement')"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       data-method="DELETE"
                                       data-confirm-title="@lang('Please Confirm')"
                                       data-confirm-text="@lang('Are you sure that you want to delete this announcement?')"
                                       data-confirm-delete="@lang('Yes, delete it!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7"><em>@lang('No announcements found.')</em></td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {!! $announcements->render() !!}
@stop
