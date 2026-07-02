<span class="text-nowrap">
    @can('portofolio_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('portofolio.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('portofolio_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('portofolio.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
