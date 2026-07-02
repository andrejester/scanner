@forelse($unreadInbox as $mail)
    <li class="list-group-item dropdown-notifications-item">
        <a href="{{ route('inbox.show', $mail->id) }}" class="d-flex text-decoration-none">
            <div class="me-3">
                <span class="avatar-initial bg-label-primary rounded-circle">
                    <i class="bx bx-envelope"></i>
                </span>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">{{ $mail->subject ?? 'No Subject' }}</h6>
                <p class="text-muted small mb-0">{{ $mail->name }}</p>
                <small class="text-muted">{{ $mail->email }}</small>
                <br>
                <small class="text-muted">{{ $mail->created_at->diffForHumans() }}</small>
            </div>
        </a>
    </li>
@empty
    <li class="list-group-item py-3 text-center">
        <i class="bx bx-check-circle text-success mb-2" style="font-size: 2rem;"></i>
        <p class="text-muted mb-0">Tidak ada inbox baru</p>
    </li>
@endforelse
