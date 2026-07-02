<span class="text-nowrap">
    @can('user_update')
        <button class="btn btn-sm btn-icon me-2" onclick="openModal('{{ route('user.edit', $data->id) }}')"><i
                class="bx bx-edit"></i></button>
    @endcan
    @can('user_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('user.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
