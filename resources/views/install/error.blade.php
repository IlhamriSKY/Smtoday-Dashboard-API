@extends('layouts.install')

@section('content')

    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'installation' => 'selected done',
        'complete' => 'selected error'
    ]])

    <div class="step-content">
        <h3>Whoops!</h3>
        <hr>
        <p><strong>There was something wrong during the installation!</strong></p>
        <p>Please check your log located inside <code>storage/logs</code> directory to see what's going on.</p>

        <a class="btn btn-primary float-right mt-3" href="{{ route('install.start') }}">
            <i class="fa fa-undo"></i>
            Try Again
        </a>
        <div class="clearfix"></div>
    </div>

@stop