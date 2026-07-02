<span class="text-nowrap">
    @can('masteralumni_read')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('alumnitmp.show', $data->id) }}" title="Lihat Detail">
            <i class="bx bx-show"></i>
        </a>
    @endcan

    @if ($data->status == 0)
        @can('masteralumni_update')
            <button class="btn btn-sm btn-icon btn-success me-2" onclick="updateStatus({{ $data->id }}, 1)"
                title="Setujui">
                <i class="bx bx-check"></i>
            </button>
            <button class="btn btn-sm btn-icon btn-danger me-2" onclick="updateStatus({{ $data->id }}, 2)"
                title="Tolak">
                <i class="bx bx-x"></i>
            </button>
        @endcan
    @endif

    @can('masteralumni_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('alumnitmp.destroy', $data->id) }}')"
            title="Hapus">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
