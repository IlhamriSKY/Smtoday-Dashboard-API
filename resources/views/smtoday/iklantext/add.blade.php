@extends('layouts.app')

@section('page-title', __('Iklan Text'))
@section('page-heading', $edit ? $iklantext->judul : __('Create New Iklan'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('iklantext.index') }}">@lang('Iklan Text')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ __($edit ? 'Edit' : 'Create') }}
    </li>
@stop

@section('content')

@include('partials.messages')

@if ($edit)
    {!! Form::open(['route' => ['iklantext.update', $iklantext], 'method' => 'PUT', 'id' => 'iklantext-form']) !!}
@else
    {!! Form::open(['route' => 'iklantext.store', 'id' => 'iklantext-form']) !!}
@endif

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h5 class="card-title">
                    @if ($edit)
                        @lang('Edit Iklan')
                    @else
                        @lang('Add Iklan')
                    @endif
                </h5>
                <p class="text-muted font-weight-light">
                    @lang('Iklan berbentuk text')
                </p>
            </div>
            <div class="col-md-9">
                <div class="form-group">
                    <label for="judul">@lang('Judul')</label>
                    <input type="text"
                           class="form-control input-solid"
                           id="judul"
                           name="judul"
                           placeholder="@lang('Judul')"
                           value="{{ $edit ? $iklantext->judul : old('judul') }}">
                </div>
                <div class="form-group">
                    <label for="text">@lang('Text')</label>
                    <textarea name="text"
                              id="text"
                              placeholder="@lang('Text')"
                              class="form-control input-solid">{{ $edit ? $iklantext->text : old('text') }}</textarea>
                </div>
                @if ($edit)
                    <div class="form-group">
                        <label for="status">@lang('Status')</label>
                        {!! Form::select('status', $statuses, $edit ? $iklantext->status : '',
                            ['class' => 'form-control input-solid', 'id' => 'status']) !!}
                    </div>
                @else
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    {{ __($edit ? "Update Iklan" : "Create Iklan") }}
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('scripts')
    @if ($edit)
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Smtoday\Iklantext\UpdateIklantextRequest', '#iklantext-form') !!}
    @else
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Smtoday\Iklantext\CreateIklantextRequest', '#iklantext-form') !!}
    @endif
@stop
