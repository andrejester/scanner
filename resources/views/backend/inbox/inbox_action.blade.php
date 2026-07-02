<span class="text-nowrap">
    @can('inbox_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('inbox.show', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('inbox_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('inbox.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
