@forelse($pendingAlumni as $alumni)
    <li class="list-group-item dropdown-notifications-item">
        <a href="{{ route('alumnitmp.show', $alumni->id) }}" class="d-flex text-decoration-none">
            <div class="me-3">
                <span class="avatar-initial bg-label-warning rounded-circle">
                    <i class="bx bx-user-plus"></i>
                </span>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">Pendaftaran Alumni Baru</h6>
                <p class="text-muted small mb-0">{{ $alumni->nama }}</p>
                <small class="text-muted">{{ $alumni->program_studi }} ({{ strtoupper($alumni->jenjang) }})</small>
                <br>
                <small class="text-muted">{{ $alumni->created_at->diffForHumans() }}</small>
            </div>
        </a>
    </li>
@empty
    <li class="list-group-item py-3 text-center">
        <i class="bx bx-check-circle text-success mb-2" style="font-size: 2rem;"></i>
        <p class="text-muted mb-0">Tidak ada pendaftaran alumni baru</p>
    </li>
@endforelse
