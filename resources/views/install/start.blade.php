@extends('layouts.install')

@section('content')

    @include('install.steps', ['steps' => ['welcome' => 'selected']])

    <div class="step-content">
        <h3>Welcome</h3>
        <hr>
        <p>This steps will guide you through few step installation process.</p>
        <p>When this installation process is finished, you will be able
            to login and manage your users immediately! </p>
        <br>
        <a href="{{ route('install.requirements') }}" class="btn btn-primary float-right" role="button">
            Next
            <i class="fa fa-arrow-right"></i>
        </a>
        <div class="clearfix"></div>
    </div>
@stop