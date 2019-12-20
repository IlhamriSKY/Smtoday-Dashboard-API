<tr>
    <td class="align-middle">
        {{ $beritatext->judul ?: __('N/A') }}
    </td>
    <td class="align-middle">{{ $beritatext->text }}</td>
    <td class="align-middle">
        <span class="badge badge-lg badge-{{ $beritatext->present()->labelClass }}">
            {{ trans("app.status.{$beritatext->status}") }}
        </span>
    </td>

    <td class="align-middle">{{ $beritatext->created_at->format(config('app.date_format')) }}</td>
    <td class="text-center align-middle">
        @permission('user.edit')
        <a href="{{ route('beritatext.edit', $beritatext) }}"
           class="btn btn-icon edit"
           title="@lang('Edit Berita')"
           data-toggle="tooltip" data-placement="top">
            <i class="fas fa-edit"></i>
        </a>
        @endpermission

        @permission('user.remove')
        <a href="{{ route('beritatext.destroy', $beritatext) }}"
           class="btn btn-icon"
           title="@lang('Delete Berita')"
           data-toggle="tooltip"
           data-placement="top"
           data-method="DELETE"
           data-confirm-title="@lang('Please Confirm')"
           data-confirm-text="@lang('Are you sure that you want to delete this berita?')"
           data-confirm-delete="@lang('Yes, delete it!')">
            <i class="fas fa-trash"></i>
        </a>
        @endpermission
    </td>
</tr>
