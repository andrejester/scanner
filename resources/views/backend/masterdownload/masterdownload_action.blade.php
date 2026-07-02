<span class="text-nowrap">
    @can('masterdownload_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterdownload.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('masterdownload_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('masterdownload.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
