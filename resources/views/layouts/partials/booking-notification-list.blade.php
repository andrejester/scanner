<li class="dropdown-notifications-list scrollable-container">

    <div class="tab-content p-0">

        {{-- TAB BOOKING --}}
        <div class="tab-pane fade show active" id="booking-pane" role="tabpanel">

            <ul class="list-group list-group-flush">
                @forelse($pendingBookings as $booking)
                    <li class="list-group-item dropdown-notifications-item">
                        <div class="d-flex">
                            <div class="me-3">
                                <span class="avatar-initial bg-label-warning rounded">
                                    <i class="bx bx-calendar"></i>
                                </span>
                            </div>
                            <div>
                                <strong>Booking Baru</strong><br>
                                <small>{{ $booking->nama ?? '-' }}</small>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item py-3 text-center">
                        <small class="text-muted">Tidak ada booking</small>
                    </li>
                @endforelse
            </ul>

        </div>

        {{-- TAB INBOX --}}
        <div class="tab-pane fade" id="inbox-pane" role="tabpanel">

            <ul class="list-group list-group-flush">
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
                                <small>{{ $mail->subject ?? '-' }}</small>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item py-3 text-center">
                        <small class="text-muted">Tidak ada inbox baru</small>
                    </li>
                @endforelse
            </ul>

        </div>

    </div>

</li>
