@extends('layouts.app')

@section('page-title', __('Announcements'))
@section('page-heading', __('Announcements'))

@section('breadcrumbs')
    <li class="breadcrumb-item active">
        @lang('Announcements')
    </li>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto mb-4">
            @foreach ($announcements as $announcement)
                @include('announcements::partials.card')
            @endforeach

            @if (count($announcements) == 0)
                <div class="card">
                    <div class="card-body">
                        <p class="lead text-center m-0">
                            @lang('No new announcements at the moment.')
                        </p>
                    </div>
                </div>
            @endif

            {!! $announcements->render() !!}
        </div>
    </div>

@stop
