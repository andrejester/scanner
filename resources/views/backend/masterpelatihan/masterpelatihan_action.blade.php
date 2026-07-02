<span class="text-nowrap">
    @can('masterpelatihan_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterpelatihan.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('masterpelatihan_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('masterpelatihan.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
