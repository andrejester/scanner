<span class="text-nowrap">
    @can('banner_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('banner.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('banner_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('banner.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
