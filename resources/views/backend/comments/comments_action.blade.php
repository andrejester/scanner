<span class="text-nowrap">
    @can('comments_read')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('comments.show', $data->id) }}" title="View">
            <i class="bx bx-show"></i>
        </a>
    @endcan
    @can('comments_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick="hapus('{{ route('comments.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
