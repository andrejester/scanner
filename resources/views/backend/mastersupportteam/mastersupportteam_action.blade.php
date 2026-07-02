<span class="text-nowrap">
    @can('mastersupportteam_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('mastersupportteam.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('mastersupportteam_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('mastersupportteam.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
