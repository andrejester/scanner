<span class="text-nowrap">
    @can('masterkategoriberita_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterkategoriberita.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('masterkategoriberita_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('masterkategoriberita.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
