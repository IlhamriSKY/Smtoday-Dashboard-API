@if ($item && $item->authorize(auth()->user()))
<li class="nav-item">
    <a class="nav-link {{ Request::is($item->getActivePath()) ? 'active' : '' }}"
       href="{{ $item->getHref() }}"
       @if($item->isDropdown())
       data-toggle="collapse"
       aria-expanded="{{ Request::is($item->getExpandedPath()) ? 'true' : 'false' }}"
        @endif
    >
        @if ($item->getIcon())
            <i class="{{ $item->getIcon() }}"></i>
        @endif

        <span>{{ $item->getTitle() }}</span>
    </a>

    @if ($item->isDropdown())
        <ul class="{{ Request::is($item->getExpandedPath()) ? '' : 'collapse' }} list-unstyled sub-menu"
            id="{{ str_replace('#', '', $item->getHref()) }}">
            @foreach ($item->children() as $child)
                @include('partials.sidebar.items', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
@endif
