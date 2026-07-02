<span class="text-nowrap">
    @can('logoPerusahaan_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('logoPerusahaan.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('logoPerusahaan_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('logoPerusahaan.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
