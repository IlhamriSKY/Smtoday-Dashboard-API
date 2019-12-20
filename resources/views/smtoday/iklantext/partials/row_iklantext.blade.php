<tr>
    <td class="align-middle">
        {{ $iklantext->judul ?: __('N/A') }}
    </td>
    <td class="align-middle">{{ $iklantext->text }}</td>
    <!-- <td class="align-middle">{{ $iklantext->status }}</td> -->

    <td class="align-middle">
        <span class="badge badge-lg badge-{{ $iklantext->present()->labelClass }}">
            {{ trans("app.status.{$iklantext->status}") }}
        </span>
    </td>

    <td class="align-middle">{{ $iklantext->created_at->format(config('app.date_format')) }}</td>
    <td class="text-center align-middle">
        <!-- <div class="dropdown show d-inline-block">
            <a class="btn btn-icon"
               href="#" role="button" id="dropdownMenuLink"
               data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                @if (config('session.driver') == 'database')
                    <a href="{{ route('user.sessions', $iklantext) }}" class="dropdown-item text-gray-500">
                        <i class="fas fa-list mr-2"></i>
                        @lang('User Sessions')
                    </a>
                @endif

                @permission('user.view')
                <a href="{{ route('users.show', $iklantext) }}" class="dropdown-item text-gray-500">
                    <i class="fas fa-eye mr-2"></i>
                    @lang('View User')
                </a>
                @endpermission
            </div>
        </div> -->

        @permission('user.edit')
        <a href="{{ route('iklantext.edit', $iklantext) }}"
           class="btn btn-icon edit"
           title="@lang('Edit Iklan')"
           data-toggle="tooltip" data-placement="top">
            <i class="fas fa-edit"></i>
        </a>
        @endpermission

        @permission('user.remove')
        <a href="{{ route('iklantext.destroy', $iklantext) }}"
           class="btn btn-icon"
           title="@lang('Delete Iklan')"
           data-toggle="tooltip"
           data-placement="top"
           data-method="DELETE"
           data-confirm-title="@lang('Please Confirm')"
           data-confirm-text="@lang('Are you sure that you want to delete this iklan?')"
           data-confirm-delete="@lang('Yes, delete it!')">
            <i class="fas fa-trash"></i>
        </a>
        @endpermission
    </td>
</tr>
