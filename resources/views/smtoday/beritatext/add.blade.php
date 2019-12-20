@extends('layouts.app')

@section('page-title', __('Berita Text'))
@section('page-heading', $edit ? $beritatext->nama : __('Create New Berita'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('beritatext.index') }}">@lang('Berita Text')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ __($edit ? 'Edit' : 'Create') }}
    </li>
@stop

@section('content')

@include('partials.messages')

@if ($edit)
    {!! Form::open(['route' => ['beritatext.update', $beritatext], 'method' => 'PUT', 'id' => 'beritatext-form']) !!}
@else
    {!! Form::open(['route' => 'beritatext.store', 'id' => 'beritatext-form']) !!}
@endif

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h5 class="card-title">
                    @if ($edit)
                        @lang('Edit Berita')
                    @else
                        @lang('Add Berita')
                    @endif
                </h5>
                <p class="text-muted font-weight-light">
                    @lang('Berita berbentuk text')
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
                           value="{{ $edit ? $beritatext->judul : old('judul') }}">
                </div>
                <div class="form-group">
                    <label for="text">@lang('Text')</label>
                    <textarea name="text"
                              id="text"
                              placeholder="@lang('Text')"
                              class="form-control input-solid">{{ $edit ? $beritatext->text : old('text') }}</textarea>
                </div>
                @if ($edit)
                    <div class="form-group">
                        <label for="status">@lang('Status')</label>
                        {!! Form::select('status', $statuses, $edit ? $beritatext->status : '',
                            ['class' => 'form-control input-solid', 'id' => 'status']) !!}
                    </div>
                @else
                @endif
            </div>
        </div>
            <div class="row">
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __($edit ? "Update Berita" : "Create Berita") }}
                    </button>
                </div>
            </div>
    </div>
</div>

@stop

@section('scripts')
    @if ($edit)
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Smtoday\Beritatext\UpdateBeritatextRequest', '#beritatext-form') !!}
    @else
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Smtoday\Beritatext\CreateBeritatextRequest', '#beritatext-form') !!}
    @endif
@stop
