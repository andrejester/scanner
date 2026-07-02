<span class="text-nowrap">
    @can('notes_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('notes.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('notes_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('notes.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
