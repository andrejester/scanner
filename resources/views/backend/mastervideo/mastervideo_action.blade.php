<span class="text-nowrap">
    @can('mastervideo_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('mastervideo.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('mastervideo_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('mastervideo.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
