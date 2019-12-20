<tr>
    <td style="width: 40px;">
        <a href="{{ route('users.show', $user) }}">
            <img
                class="rounded-circle img-responsive"
                width="40"
                src="{{ $user->present()->avatar }}"
                alt="{{ $user->present()->name }}">
        </a>
    </td>
    <td class="align-middle">
        <a href="{{ route('users.show', $user) }}">
            {{ $user->username ?: __('N/A') }}
        </a>
    </td>
    <td class="align-middle">{{ $user->first_name . ' ' . $user->last_name }}</td>
    <td class="align-middle">{{ $user->email }}</td>
    <td class="align-middle">{{ $user->created_at->format(config('app.date_format')) }}</td>
    <td class="align-middle">
        <span class="badge badge-lg badge-{{ $user->present()->labelClass }}">
            {{ trans("app.status.{$user->status}") }}
        </span>
    </td>
    <td class="text-center align-middle">
        <div class="dropdown show d-inline-block">
            <a class="btn btn-icon"
               href="#" role="button" id="dropdownMenuLink"
               data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                @if (config('session.driver') == 'database')
                    <a href="{{ route('user.sessions', $user) }}" class="dropdown-item text-gray-500">
                        <i class="fas fa-list mr-2"></i>
                        @lang('User Sessions')
                    </a>
                @endif

                @permission('user.view')
                <a href="{{ route('users.show', $user) }}" class="dropdown-item text-gray-500">
                    <i class="fas fa-eye mr-2"></i>
                    @lang('View User')
                </a>
                @endpermission

                @canBeImpersonated($user)
                    <a href="{{ route('impersonate', $user) }}" class="dropdown-item text-gray-500 impersonate">
                        <i class="fas fa-user-secret mr-2"></i>
                        @lang('Impersonate')
                    </a>
                @endCanBeImpersonated
            </div>
        </div>

        @permission('user.edit')
        <a href="{{ route('users.edit', $user) }}"
           class="btn btn-icon edit"
           title="@lang('Edit User')"
           data-toggle="tooltip" data-placement="top">
            <i class="fas fa-edit"></i>
        </a>
        @endpermission

        @permission('user.remove')
        <a href="{{ route('users.destroy', $user) }}"
           class="btn btn-icon"
           title="@lang('Delete User')"
           data-toggle="tooltip"
           data-placement="top"
           data-method="DELETE"
           data-confirm-title="@lang('Please Confirm')"
           data-confirm-text="@lang('Are you sure that you want to delete this user?')"
           data-confirm-delete="@lang('Yes, delete him!')">
            <i class="fas fa-trash"></i>
        </a>
        @endpermission
    </td>
</tr>
