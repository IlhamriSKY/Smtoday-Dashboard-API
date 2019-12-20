@extends('layouts.app')

@section('page-title', __('Permissions'))
@section('page-heading', $edit ? $permission->name : __('Create New Permission'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('permissions.index') }}">@lang('Permissions')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ __($edit ? 'Edit' : 'Create') }}
    </li>
@stop

@section('content')

@include('partials.messages')

@if ($edit)
    {!! Form::open(['route' => ['permissions.update', $permission], 'method' => 'PUT', 'id' => 'permission-form']) !!}
@else
    {!! Form::open(['route' => 'permissions.store', 'id' => 'permission-form']) !!}
@endif

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h5 class="card-title">
                    @lang('Permission Details')
                </h5>
                <p class="text-muted font-weight-light">
                    @lang('A general permission information.')
                </p>
            </div>
            <div class="col-md-9">
                <div class="form-group">
                    <label for="name">@lang('Name')</label>
                    <input type="text"
                           class="form-control input-solid"
                           id="name"
                           name="name"
                           placeholder="@lang('Permission Name')"
                           value="{{ $edit ? $permission->name : old('name') }}">
                </div>
                <div class="form-group">
                    <label for="display_name">@lang('Display Name')</label>
                    <input type="text"
                           class="form-control input-solid"
                           id="display_name"
                           name="display_name"
                           placeholder="@lang('Display Name')"
                           value="{{ $edit ? $permission->display_name : old('display_name') }}">
                </div>
                <div class="form-group">
                    <label for="description">@lang('Description')</label>
                    <textarea name="description"
                              id="description"
                              class="form-control input-solid">{{ $edit ? $permission->description : old('description') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary">
            {{ __($edit ? "Update Permission" : "Create Permission") }}
        </button>
    </div>
</div>

@stop

@section('scripts')
    @if ($edit)
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Permission\UpdatePermissionRequest', '#permission-form') !!}
    @else
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Permission\CreatePermissionRequest', '#permission-form') !!}
    @endif
@stop
