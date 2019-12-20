@extends('layouts.app')

@section('page-title', __('Announcement') . ":" . $announcement->title)
@section('page-heading', __('Announcement Details'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('announcements.list') }}">
            @lang('Announcements')
        </a>
    </li>
    <li class="breadcrumb-item active">
        {{ __('Announcement Details') }}
    </li>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto mb-4">
            @include('announcements::partials.card')
        </div>
    </div>
@stop

