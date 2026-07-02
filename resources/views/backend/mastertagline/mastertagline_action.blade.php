<span class="text-nowrap">
    @can('mastertagline_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('mastertagline.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('mastertagline_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick="hapus('{{ route('mastertagline.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
