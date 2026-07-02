@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Comments /</span> Detail
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Comment Details</h5>
                    <a href="{{ route('comments.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Author:</label>
                        <p>
                            @if ($comment->user)
                                {{ $comment->user->name }} ({{ $comment->user->email }})
                            @else
                                {{ $comment->name ?? 'Guest' }} {{ $comment->email ? '(' . $comment->email . ')' : '' }}
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content Type:</label>
                        <p>
                            @if ($comment->blog_id)
                                Blog: <a href="{{ route('blog.details', $comment->blog->slug ?? '#') }}" target="_blank">
                                    {{ $comment->blog->title ?? 'N/A' }}
                                </a>
                            @elseif ($comment->video_id)
                                Video ID: {{ $comment->video_id }}
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Comment:</label>
                        <div class="bg-light rounded p-3">
                            {!! nl2br(e($comment->comment)) !!}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Date:</label>
                        <p>{{ $comment->created_at->format('d M Y H:i') }}</p>
                    </div>

                    @can('comments_update')
                        <form action="{{ route('comments.updateStatus', $comment->id) }}" method="POST" class="mt-4">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status:</label>
                                    <select name="status" class="form-control">
                                        <option value="pending" {{ $comment->status == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="active" {{ $comment->status == 'active' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="inactive" {{ $comment->status == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Update Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endcan
                </div>
            </div>

            @if ($comment->replies->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Replies ({{ $comment->replies->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($comment->replies as $reply)
                            <div class="border-bottom mb-3 pb-3">
                                <div class="d-flex justify-content-between">
                                    <strong>
                                        @if ($reply->user)
                                            {{ $reply->user->name }}
                                        @else
                                            {{ $reply->name ?? 'Guest' }}
                                        @endif
                                    </strong>
                                    <small class="text-muted">{{ $reply->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <p class="mb-0 mt-2">{!! nl2br(e($reply->comment)) !!}</p>
                                <span
                                    class="badge bg-{{ $reply->status == 'active' ? 'success' : ($reply->status == 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($reply->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    @can('comments_delete')
                        <button class="btn btn-danger w-100 mb-2"
                            onclick="if(confirm('Are you sure?')) { window.location.href='{{ route('comments.destroy', $comment->id) }}' }">
                            <i class="bx bx-trash"></i> Delete Comment
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
