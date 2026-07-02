<span class="text-nowrap">
    @can('masterkategorijurnal_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterkategorijurnal.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('masterkategorijurnal_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick="hapus('{{ route('masterkategorijurnal.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
