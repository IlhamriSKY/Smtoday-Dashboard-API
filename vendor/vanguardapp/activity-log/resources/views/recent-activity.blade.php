<div class="card">
    <h6 class="card-header d-flex align-items-center justify-content-between">
        @lang('Latest Activity')

        @if (count($activities))
            <small>
                <a href="{{ route('activity.user', $user->id) }}"
                   class="edit"
                   data-toggle="tooltip"
                   data-placement="top"
                   title="@lang('Complete Activity Log')">
                    @lang('View All')
                </a>
            </small>
        @endif
    </h6>

    <div class="card-body">
        @if (count($activities))
            <table class="table table-borderless table-striped">
                <thead>
                <tr>
                    <th>@lang('Action')</th>
                    <th>@lang('Date')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($activities as $activity)
                    <tr>
                        <td>{{ $activity->description }}</td>
                        <td>{{ $activity->created_at->format(config('app.date_time_format')) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted font-weight-light">
                <em>@lang('No activity from this user yet.')</em>
            </p>
        @endif
    </div>
</div>
