<span class="text-nowrap">
    @can('pelatihanadmin_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('pelatihanadmin.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('pelatihanadmin_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('pelatihanadmin.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
