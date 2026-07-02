<span class="text-nowrap">
    @can('downloads_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('downloads.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('downloads_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('downloads.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
