<span class="text-nowrap">
    @can('mastergaleri_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('mastergaleri.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('mastergaleri_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('mastergaleri.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
