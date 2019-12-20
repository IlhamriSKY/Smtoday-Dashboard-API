<li class="nav-item dropdown announcements d-flex align-items-center px-3" id="announcements-icon">
    <a href="#"
       class="text-gray-500 position-relative nav-icon"
       id="announcementsDropdown"
       role="button"
       data-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
        @if (count($announcements) > 0 && $announcements->first()->wasReadBy(auth()->user()))
            <i class="activity-badge"></i>
        @endif
        <i class="fas fa-bullhorn"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right position-absolute p-0 shadow-lg"
         aria-labelledby="announcementsDropdown"
         style="width: 380px; height: 350px; overflow-y: scroll; overflow-x: hidden;">
        <div class="text-center p-4">
            <h5 class="text-muted mt-2">
                @lang('Announcements')
            </h5>
            @if (count($announcements) > 0)
                <a href="{{ route('announcements.list') }}">
                    @lang('View All')
                </a>
            @endif
        </div>
        <div class="bg-lighter">
            @if (count($announcements) > 0)
                @foreach ($announcements as $announcement)
                    @include('announcements::partials.navbar.item')
                @endforeach
            @else
                <p class="text-center">@lang('No new announcements at the moment.')</p>
            @endif
        </div>

    </div>
</li>
