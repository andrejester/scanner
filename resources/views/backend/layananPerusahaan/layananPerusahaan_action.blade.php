<span class="text-nowrap">
    @can('layananPerusahaan_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('layananPerusahaan.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('layananPerusahaan_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('layananPerusahaan.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
