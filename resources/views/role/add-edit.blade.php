@extends('layouts.app')

@section('page-title', __('Roles'))
@section('page-heading', $edit ? $role->name : __('Create New Role'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('roles.index') }}">@lang('Roles')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ __($edit ? 'Edit' : 'Create') }}
    </li>
@stop

@section('content')

@include('partials.messages')

@if ($edit)
    {!! Form::open(['route' => ['roles.update', $role], 'method' => 'PUT', 'id' => 'role-form']) !!}
@else
    {!! Form::open(['route' => 'roles.store', 'id' => 'role-form']) !!}
@endif

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h5 class="card-title">
                    @lang('Role Details')
                </h5>
                <p class="text-muted">
                    @lang('A general role information.')
                </p>
            </div>
            <div class="col-md-9">
                <div class="form-group">
                    <label for="name">@lang('Name')</label>
                    <input type="text"
                           class="form-control input-solid"
                           id="name"
                           name="name"
                           placeholder="@lang('Role Name')"
                           value="{{ $edit ? $role->name : old('name') }}">
                </div>
                <div class="form-group">
                    <label for="display_name">@lang('Display Name')</label>
                    <input type="text"
                           class="form-control input-solid"
                           id="display_name"
                           name="display_name"
                           placeholder="@lang('Display Name')"
                           value="{{ $edit ? $role->display_name : old('display_name') }}">
                </div>
                <div class="form-group">
                    <label for="description">@lang('Description')</label>
                    <textarea name="description"
                              id="description"
                              class="form-control input-solid">{{ $edit ? $role->description : old('description') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">
    {{ __($edit ? 'Update Role' : 'Create Role') }}
</button>

@stop

@section('scripts')
    @if ($edit)
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Role\UpdateRoleRequest', '#role-form') !!}
    @else
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Role\CreateRoleRequest', '#role-form') !!}
    @endif
@stop
