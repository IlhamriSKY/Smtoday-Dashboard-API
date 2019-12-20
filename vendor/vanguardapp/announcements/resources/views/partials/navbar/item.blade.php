<div class="d-flex align-items-start px-4 p-4 navbar-item">
    <img src="{{ $announcement->creator->present()->avatar }}" width="50" height="50"
         class="rounded-circle img-responsive mr-3">

    <div class="w-100">
        <div class="d-flex justify-content-between align-items-start">
            <span class="font-weight-bold">
                {{ $announcement->creator->present()->name }}
            </span>
            <span class="text-muted">
                {{ $announcement->created_at->diffForHumans(null, true, true) }}
            </span>
        </div>

        <a href="{{ route('announcements.show', $announcement) }}">
            {{ $announcement->title }}
        </a>
    </div>
</div>
