@extends('layouts.app')

@section('page-title', $iklanimage->present()->namaiklanimage)
@section('page-heading', $iklanimage->present()->namaiklanimage)

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('iklanimage.index') }}">@lang('Iklan Image')</a>
    </li>
    <li class="breadcrumb-item active">
        {{ $iklanimage->present()->namaiklanimage }}
    </li>
@stop

@section('content')

<div class="row">
    <div class="col-lg-5 col-xl-4 mx-auto">
        <div class="card">
            <h6 class="card-header d-flex align-items-center justify-content-between">
                @lang('Details')

                <small>
                    <a href="{{ route('iklanimage.edit', $iklanimage) }}"
                       class="edit"
                       data-toggle="tooltip"
                       data-placement="top"
                       title="@lang('Edit Iklan')">
                        @lang('Edit')
                    </a>
                </small>
            </h6>
            <div class="card-body">
               <div class="d-flex align-items-center flex-column pt-3">
                </div>

                <ul class="list-group list-group-flush mt-3">
                    <li class="list-group-item">
                        <strong>@lang('Judul'):</strong>
                        {{ $iklanimage->present()->namaiklanimage }}
                    </li>
                    <li class="list-group-item">
                        <strong>@lang('Image'):</strong>
                        {{ $iklanimage->present()->image }}
                    </li>
                </ul>
            </div>
        </div>
    </div>

        <div class="col-lg-7 col-xl-8">
            <div class="card">
                <img class="img-thumbnail img-responsive" src="{{ $iklanimage->present()->image }}">
            </div>
        </div>

</div>
@stop
