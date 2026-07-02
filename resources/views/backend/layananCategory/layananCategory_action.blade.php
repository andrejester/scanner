<span class="text-nowrap">
    @can('layananCategory_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('layananCategory.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('layananCategory_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('layananCategory.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
