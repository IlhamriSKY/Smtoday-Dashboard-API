@extends('layouts.app')

@section('page-title', __('Permissions'))
@section('page-heading', __('Permissions'))

@section ('breadcrumbs')
    <li class="breadcrumb-item active">
        @lang('Permissions')
    </li>
@stop

@section('content')

@include('partials.messages')

{!! Form::open(['route' => 'permissions.save', 'class' => 'mb-4']) !!}

<div class="card">
    <div class="card-body">

        <div class="row mb-3 pb-3 border-bottom-light">
            <div class="col-lg-12">
                <div class="float-right">
                    <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-rounded">
                        <i class="fas fa-plus mr-2"></i>
                        @lang('Add Permission')
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive" id="users-table-wrapper">
            <table class="table table-striped table-borderless">
                <thead>
                    <tr>
                        <th class="min-width-200">@lang('Name')</th>
                        @foreach ($roles as $role)
                            <th class="text-center">{{ $role->display_name }}</th>
                        @endforeach
                        <th class="text-center min-width-100">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                @if (count($permissions))
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->display_name ?: $permission->name }}</td>

                            @foreach ($roles as $role)
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        {!!
                                            Form::checkbox(
                                                "roles[{$role->id}][]",
                                                $permission->id,
                                                $role->hasPermission($permission->name),
                                                [
                                                    'class' => 'custom-control-input',
                                                    'id' => "cb-{$role->id}-{$permission->id}"
                                                ]
                                            )
                                        !!}
                                        <label class="custom-control-label d-inline"
                                               for="cb-{{ $role->id }}-{{ $permission->id }}"></label>
                                    </div>
                                </td>
                            @endforeach

                            <td class="text-center">
                                <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-icon"
                                   title="@lang('Edit Permission')" data-toggle="tooltip" data-placement="top">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if ($permission->removable)
                                    <a href="{{ route('permissions.destroy', $permission) }}" class="btn btn-icon"
                                       title="@lang('Delete Permission')"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       data-method="DELETE"
                                       data-confirm-title="@lang('Please Confirm')"
                                       data-confirm-text="@lang('Are you sure that you want to delete this permission?')"
                                       data-confirm-delete="@lang('Yes, delete it!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4"><em>@lang('No records found.')</em></td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@if (count($permissions))
    <div class="row">
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">@lang('Save Permissions')</button>
        </div>
    </div>
@endif

{!! Form::close() !!}

@stop
