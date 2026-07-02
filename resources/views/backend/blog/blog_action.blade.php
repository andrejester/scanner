<span class="text-nowrap">
    @can('blog_update')
        <a class="btn btn-sm btn-icon me-2" href="{{ route('blogadmin.edit', $data->id) }}"><i class="bx bx-edit"></i></a>
    @endcan
    @can('blog_delete')
        <button class="btn btn-sm btn-icon delete-record" onclick=" hapus('{{ route('blogadmin.destroy', $data->id) }}')"><i
                class="bx bx-trash"></i></button>
    @endcan
</span>
