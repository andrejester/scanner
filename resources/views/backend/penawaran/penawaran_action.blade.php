<span class="text-nowrap">
    @can('penawaran_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('penawaran.show', $data->id) }}"><i class="bx bx-scan"></i></a>
    @endcan
    @can('penawaran_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('penawaran.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
