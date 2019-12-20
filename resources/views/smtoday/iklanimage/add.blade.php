@extends('layouts.app')

@section('page-title', __('Iklan Image'))
@section('page-heading', $edit ? $iklanimage->judul : __('Create New Iklan'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('iklanimage.index') }}">@lang('Iklan Image')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ __($edit ? 'Edit' : 'Create') }}
    </li>
@stop

@section('content')

@include('partials.messages')


<div class="section">
    @if ($edit)
        {!! Form::open(['route' => ['iklanimage.update', $iklanimage], 'method' => 'PUT', 'id' => 'iklanimage-form']) !!}
    @else
        <form action="{{ route('upload.image') }}" method="post" enctype="multipart/form-data">
    @endif
    @csrf
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
                        @lang('Iklan berbentuk gambar')
                    </p>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="judul">@lang('Jama')</label>
                        <input type="text"
                            class="form-control input-solid"
                            id="judul"
                            name="judul"
                            placeholder="@lang('Judul')"
                            value="{{ $edit ? $iklanimage->judul : old('judul') }}">
                    </div>

                    @if ($edit)
                    @else
                    <div class="form-group">
                        <label for="image">@lang('Image')</label>
                        <input type="file"
                            class="form-control input-solid"
                            id="image"
                            name="image"
                            placeholder="@lang('Image')"
                            value="{{ $edit ? $iklanimage->image : old('image') }}">
                            <p class="text-danger">{{ $errors->first('image') }}</p>
                    </div>
                    @endif

                    @if ($edit)
                        <div class="form-group">
                            <label for="status">@lang('Status')</label>
                            {!! Form::select('status', $statuses, $edit ? $iklanimage->status : '',
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
    </form>
</div>

@stop

@section('scripts')
    @if ($edit)
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Smtoday\Iklanimage\UpdateIklanimageRequest', '#iklanimage-form') !!}
    @else
        {!! JsValidator::formRequest('Vanguard\Http\Requests\Smtoday\Iklanimage\CreateIklanimageRequest', '#iklanimage-form') !!}
    @endif
@stop
