<span class="text-nowrap">
    @can('masteralumni_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masteralumni.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('masteralumni_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('masteralumni.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
