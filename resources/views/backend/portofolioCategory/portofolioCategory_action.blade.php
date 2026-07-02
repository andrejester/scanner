<span class="text-nowrap">
    @can('portofolioCategory_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('portofolioCategory.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('portofolioCategory_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('portofolioCategory.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
