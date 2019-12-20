<div class="card overflow-hidden shadow-lg">
    <div class="card-body p-0">
        <div class="p-4">
            <h4 class="card-title mb-3">
                {{ $announcement->title }}
            </h4>
            <div>
                {{ $announcement->parsed_body }}
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center px-4 py-3 bg-lighter">
            <div class="d-flex align-items-center ">
                <img
                    class="rounded-circle img-responsive mr-2"
                    width="40"
                    src="{{ $announcement->creator->present()->avatar }}"
                    alt="{{ $announcement->creator->present()->name }}">

                <div>
                    <div class="line-height-1">
                        {{ $announcement->creator->present()->name }}
                    </div>
                    <div class="text-muted">
                        {{ $announcement->created_at->format('M d') }}
                    </div>
                </div>
            </div>
            @permission('announcements.manage')
                <div>
                    <a href="{{ route('announcements.edit', $announcement) }}"
                       class="btn btn-secondary btn-sm">
                        @lang('Edit')
                    </a>
                </div>
            @endpermission
        </div>
    </div>
</div>
