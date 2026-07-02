<span class="text-nowrap">
    @can('facilities_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('facilities.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('facilities_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('facilities.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
