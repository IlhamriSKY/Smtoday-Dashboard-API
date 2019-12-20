<tr>
    <td class="align-middle">
        {{ $iklanimage->judul ?: __('N/A') }}
    </td>

    <td style="width: 40px;">
        <a href="{{ route('iklanimage.show', $iklanimage) }}">
            <img
                class="img-responsive"
                width="100%"
                src="{{ $iklanimage->present()->image }}"
                alt="{{ $iklanimage->present()->judul }}">
        </a>
    </td>

    <td class="align-middle">
        <span class="badge badge-lg badge-{{ $iklanimage->present()->labelClass }}">
            {{ trans("app.status.{$iklanimage->status}") }}
        </span>
    </td>

    <td class="align-middle">{{ $iklanimage->created_at->format(config('app.date_format')) }}</td>
    <td class="text-center align-middle">
        @permission('user.view')
            <a href="{{ route('iklanimage.show', $iklanimage) }}"
            class="btn btn-icon edit"
            title="@lang('View Iklan')"
            data-toggle="tooltip" data-placement="top">
                <i class="fas fa-eye"></i>
            </a>
        @endpermission

        @permission('user.edit')
        <a href="{{ route('iklanimage.edit', $iklanimage) }}"
           class="btn btn-icon edit"
           title="@lang('Edit Iklan')"
           data-toggle="tooltip" data-placement="top">
            <i class="fas fa-edit"></i>
        </a>
        @endpermission

        @permission('user.remove')
        <a href="{{ route('iklanimage.destroy', $iklanimage) }}"
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
