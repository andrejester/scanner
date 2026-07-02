<span class="text-nowrap">
    @can('service_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('service.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('service_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('service.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
