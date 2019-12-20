<div class="card overflow-hidden">
    <h6 class="card-header d-flex align-items-center justify-content-between">
        @lang('Latest Registrations')

        @if (count($latestRegistrations))
            <small class="float-right">
                <a href="{{ route('users.index') }}">@lang('View All')</a>
            </small>
        @endif
    </h6>

    <div class="card-body p-0">
        @if (count($latestRegistrations))
            <ul class="list-group list-group-flush">
                @foreach ($latestRegistrations as $user)
                    <li class="list-group-item list-group-item-action px-4 py-3">
                        <a href="{{ route('users.show', $user) }}" class="d-flex text-dark no-decoration">
                            <img class="rounded-circle" width="40" height="40" src="{{ $user->present()->avatar }}">
                            <div class="ml-2" style="line-height: 1.2;">
                                <span class="d-block p-0">{{ $user->present()->nameOrEmail }}</span>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">@lang('No records found.')</p>
        @endif
    </div>
</div>
