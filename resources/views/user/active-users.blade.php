@extends('layouts.app')

@section('page-title', 'Active Users')
@section('page-heading', 'Active Users')

@section('breadcrumbs')
    <li class="breadcrumb-item active">
        Active Users
    </li>
@stop

@section('content')
    @include('partials.messages')
    @foreach($users as $user)
        <div class="user media d-flex align-items-center">
            <div>
                <a href="{{ $user->present()->avatar }}">
                    <img width="64" height="64"
                        class="media-object mr-3 rounded-circle img-thumbnail img-responsive"
                        src="{{ $user->present()->avatar }}">
                </a>
            </div>
            <div class="d-flex justify-content-center flex-column">
                <h5 class="mb-0">{{ $user->present()->name }}</h5>
                <small class="text-muted">{{ $user->email }}</small>
            </div>
        </div>
    @endforeach
@stop

@section('styles')
    <style>
        .user.media {
            float: left;
            border: 1px solid #dfdfdf;
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-right: 15px;
        }
    </style>
@stop
