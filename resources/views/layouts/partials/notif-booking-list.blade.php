@forelse($pendingBookings as $booking)
    <li class="list-group-item dropdown-notifications-item">
        <div class="d-flex">
            <div class="me-3">
                <span class="avatar-initial bg-label-warning rounded">
                    <i class="bx bx-calendar"></i>
                </span>
            </div>
            <div>
                <strong>Booking Baru</strong> | <small> {{ $booking->nama_peserta ?? '-' }}</small><br>
                <small><a href="{{ route('bookingadmin.show', $booking->id) }}">{{ $booking->telepon ?? '-' }} ,
                        {{ ZFormat($booking->total_harga) ?? '-' }} ,

                        <b style="color: red; weight: bold;">{{ $booking->payment_status ?? '-' }}</b>
                    </a>
                </small>
            </div>
        </div>
    </li>
@empty
    <li class="list-group-item py-3 text-center">
        <small class="text-muted">Tidak ada booking</small>
    </li>
@endforelse
