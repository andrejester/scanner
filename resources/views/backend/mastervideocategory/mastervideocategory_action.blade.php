<span class="text-nowrap">
    @can('mastervideocategory_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('mastervideocategory.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('mastervideocategory_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick="hapus('{{ route('mastervideocategory.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
