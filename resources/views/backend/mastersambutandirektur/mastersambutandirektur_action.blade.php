<span class="text-nowrap">
    @can('mastersambutandirektur_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('mastersambutandirektur.edit', $data->id) }}">
            <i class="bx bx-edit"></i>
        </a>
    @endcan
    @can('mastersambutandirektur_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick="hapus('{{ route('mastersambutandirektur.destroy', $data->id) }}')">
            <i class="bx bx-trash"></i>
        </button>
    @endcan
</span>
