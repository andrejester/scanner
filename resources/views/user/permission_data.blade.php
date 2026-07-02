@php $count = 0; @endphp
@foreach ($data->getPermissionNames() as $permission)
    @php $count++; @endphp
    @if ($count > 3)
    @break
@endif

<span class="badge  bg-label-primary m-1">{{ $permission }}</span>
@endforeach
<a class="text-primary" href="{{ route('permission.edit', $data->id) }}">..</a>
