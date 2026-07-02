<span class="text-nowrap">
    @can('masterprivacypolicy_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterprivacypolicy.edit', $data->id) }}"><i
                class="bx bx-edit"></i></a>
    @endcan
    @can('masterprivacypolicy_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('masterprivacypolicy.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
