<span class="text-nowrap">
    @can('masterevent_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterevent.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('masterevent_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('masterevent.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
