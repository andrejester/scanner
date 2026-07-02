<span class="text-nowrap">
    @can('masterlayanan_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('masterlayanan.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('masterlayanan_delete')
        <button class="btn btn-sm btn-icon delete-record"
            onclick=" hapus('{{ route('masterlayanan.destroy', $data->id) }}')"><i class="bx bx-trash"></i></button>
    @endcan
</span>
