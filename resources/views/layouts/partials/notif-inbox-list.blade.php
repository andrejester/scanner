@forelse($unreadInbox as $mail)
    <li class="list-group-item dropdown-notifications-item">
        <div class="d-flex">
            <div class="me-3">
                <span class="avatar-initial bg-label-primary rounded">
                    <i class="bx bx-envelope"></i>
                </span>
            </div>
            <div>
                <strong>Inbox Baru</strong><br>
                <a href="{{ route('inbox.show', $mail->id) }}">{{ $mail->subject ?? '-' }}</a>
            </div>
        </div>
    </li>
@empty
    <li class="list-group-item py-3 text-center">
        <small class="text-muted">Tidak ada inbox baru</small>
    </li>
@endforelse
