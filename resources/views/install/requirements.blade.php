@extends('layouts.install')

@section('content')

    @include('install.steps', ['steps' => ['welcome' => 'selected done', 'requirements' => 'selected']])

    @if (! $allLoaded)
        <div class="alert alert-danger">
            <strong>Oh snap!</strong> Your system does not meet the requirements. You have to fix them in order to continue.
        </div>
    @endif

    <div class="step-content">
        <h3>System Requirements</h3>
        <hr>
        <ul class="list-group mb-4">
            @foreach ($requirements as $extension => $loaded)
            <li class="list-group-item {{ ! $loaded ? 'list-group-item-danger' : '' }}">
                {{ $extension }}
                @if ($loaded)
                    <span class="badge badge-success float-right"><i class="fa fa-check"></i></span>
                @else
                    <span class="badge badge-danger float-right"><i class="fa fa-times"></i></span>
                @endif
            </li>
            @endforeach
        </ul>
        @if ($allLoaded)
            <a class="btn btn-green float-right" href="{{ route('install.permissions') }}">
                Next
                <i class="fa fa-arrow-right"></i>
            </a>
        @else
            <button class="btn btn-green pull-right" disabled>
                Next
                <i class="fa fa-arrow-right"></i>
            </button>
        @endif
        <div class="clearfix"></div>
    </div>
@stop