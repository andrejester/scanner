<span class="text-nowrap">
    @can('masterkategoridownload_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterkategoridownload.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('masterkategoridownload_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('masterkategoridownload.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
