<span class="text-nowrap">
    @can('permission_update')
        <button class="btn btn-sm btn-icon me-2" onclick="openModal('{{ route('permission.edit', $data->id) }}')"><i
                class="bx bx-edit"></i></button>
    @endcan
    @can('permission_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('permission.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
