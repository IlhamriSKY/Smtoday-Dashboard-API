@extends('layouts.app')

@section('page-title', __('Berita Text'))
@section('page-heading', __('Berita Text'))

@section('breadcrumbs')
    <li class="breadcrumb-item active">
        @lang('Berita Text')
    </li>
@stop

@section('content')

@include('partials.messages')

<div class="card">
    <div class="card-body">
        <form action="" method="GET" id="users-form" class="pb-2 mb-3 border-bottom-light">
            <div class="row my-3 flex-md-row flex-column-reverse">
                <div class="col-md-4 mt-md-0 mt-2">
                    <div class="input-group custom-search-form">
                        <input type="text"
                               class="form-control input-solid"
                               name="search"
                               value="{{ Request::get('search') }}"
                               placeholder="@lang('Search for berita...')">

                            <span class="input-group-append">
                                @if (Request::has('search') && Request::get('search') != '')
                                    <a href="{{ route('beritatext.index') }}"
                                           class="btn btn-light d-flex align-items-center text-muted"
                                           role="button">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                                <button class="btn btn-light" type="submit" id="search-users-btn">
                                    <i class="fas fa-search text-muted"></i>
                                </button>
                            </span>
                    </div>
                </div>


                <div class="col-md-2 mt-2 mt-md-0">
                    {!!
                        Form::select(
                            'status',
                            $statuses,
                            Request::get('status'),
                            ['id' => 'status', 'class' => 'form-control input-solid']
                        )
                    !!}
                </div>

                <div class="col-md-6">
                    <a href="{{ route('beritatext.create') }}" class="btn btn-primary btn-rounded float-right">
                        <i class="fas fa-plus mr-2"></i>
                        @lang('Add Berita')
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive" id="users-table-wrapper">
            <table class="table table-borderless table-striped">
                <thead>
                <tr>
                    <th class="min-width-80">@lang('Judul')</th>
                    <th class="min-width-200">@lang('Text')</th>
                    <th class="min-width-100">@lang('Status')</th>
                    <th class="min-width-100">@lang('Created')</th>
                    <th class="text-center min-width-150">@lang('Action')</th>
                </tr>
                </thead>
                <tbody>
                    @if (count($beritatexts))
                        @foreach ($beritatexts as $beritatext)
                            @include('smtoday.beritatext.partials.row_beritatext')
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7"><em>@lang('No records found.')</em></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>


{!! $beritatexts->render() !!}

@stop

@section('scripts')
    <script>
        $("#status").change(function () {
            $("#beritatexts-form").submit();
        });
    </script>
@stop
