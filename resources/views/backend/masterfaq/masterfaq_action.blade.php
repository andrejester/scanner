<span class="text-nowrap">
    @can('masterfaq_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterfaq.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('masterfaq_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('masterfaq.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
