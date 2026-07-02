<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3">

    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
        data-bs-auto-close="outside">

        <i class="bx bx-bell bx-sm"></i>

        @if ($totalNotif > 0)
            <span id="notif-count" class="badge bg-danger rounded-pill badge-notifications">
                {{ $totalNotif }}
            </span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end py-0">
        <li class="dropdown-menu-header border-bottom px-3 pb-2 pt-3">

            <ul class="nav nav-tabs nav-fill" id="notifTab" role="tablist">
                {{-- Tab Inbox --}}
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox-pane"
                        type="button" role="tab">
                        Inbox
                        @if ($unreadInboxCount > 0)
                            <span class="badge bg-primary ms-1">
                                {{ $unreadInboxCount }}
                            </span>
                        @endif
                    </button>
                </li>

            </ul>

        </li>

        <li class="dropdown-notifications-list scrollable-container">

            <div class="tab-content p-0">

                {{-- TAB INBOX --}}
                <div class="tab-pane fade" id="inbox-pane" role="tabpanel">

                    <ul id="inbox-list" class="list-group list-group-flush">
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
                    </ul>

                    @if ($unreadInboxCount > 0)
                        <div class="border-top p-2">
                            <a href="{{ route('inbox.index') }}" class="btn btn-sm btn-outline-primary w-100">
                                Lihat Semua ({{ $unreadInboxCount }})
                            </a>
                        </div>
                    @endif

                </div>

            </div>

        </li>

    </ul>
</li>

<style>
    /* Notification Styles */
    .dropdown-notifications-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .dropdown-notifications-item {
        transition: background-color 0.2s;
    }

    .dropdown-notifications-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-notifications-item a {
        color: inherit;
    }

    .dropdown-notifications-item a:hover {
        text-decoration: none;
    }

    .avatar-initial {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    /* Bell Animation */
    @keyframes bell-ring {

        0%,
        100% {
            transform: rotate(0deg);
        }

        10%,
        30% {
            transform: rotate(-10deg);
        }

        20%,
        40% {
            transform: rotate(10deg);
        }
    }

    .bell-animate {
        animation: bell-ring 0.5s ease-in-out;
    }

    /* Badge Animation */
    @keyframes notif-pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }
    }

    @keyframes notif-blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .notif-animate {
        animation: notif-pulse 0.5s ease-in-out;
    }

    .notif-blink {
        animation: notif-blink 0.5s ease-in-out 3;
    }
</style>

<script>
    // =============================
    // 🔔 REAL-TIME NOTIFICATION SYSTEM
    // =============================

    let lastTotal = {{ $totalNotif ?? 0 }};
    let lastAlumniCount = {{ $pendingAlumniCount ?? 0 }};
    let isLoading = false;
    let notificationSound = null;

    // Create notification sound (optional)
    function initNotificationSound() {
        // Create a simple beep sound using Web Audio API
        const audioContext = new(window.AudioContext || window.webkitAudioContext)();

        return function playSound() {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        };
    }

    // Request browser notification permission
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // Show browser notification
    function showBrowserNotification(title, body, icon = '/assets/img/favicon/favicon-32x32.png') {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: body,
                icon: icon,
                badge: icon,
                tag: 'alumni-notification',
                requireInteraction: false
            });

            notification.onclick = function() {
                window.focus();
                notification.close();
            };

            // Auto close after 5 seconds
            setTimeout(() => notification.close(), 5000);
        }
    }

    // Main function to load notifications
    function loadNotifications() {
        if (isLoading) return;

        isLoading = true;

        fetch("{{ route('notif.pending') }}")
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                let badge = document.getElementById('notif-count');
                let currentTotal = data.total;
                let currentAlumniCount = data.alumni_count || 0;

                // =============================
                // 🔔 NOTIFIKASI BARU DETECTED
                // =============================
                if (currentTotal > lastTotal) {
                    // Bell animation
                    let bellIcon = document.querySelector('.bx-bell');
                    if (bellIcon) {
                        bellIcon.classList.remove('bell-animate');
                        void bellIcon.offsetWidth;
                        bellIcon.classList.add('bell-animate');
                    }

                    // Badge animation
                    if (badge) {
                        badge.classList.add('notif-animate', 'notif-blink');
                        setTimeout(() => {
                            badge.classList.remove('notif-animate', 'notif-blink');
                        }, 1500);
                    }

                    // Play sound (if initialized)
                    if (notificationSound) {
                        try {
                            notificationSound();
                        } catch (e) {
                            console.log('Sound play failed:', e);
                        }
                    }

                    // Show browser notification for new alumni
                    if (currentAlumniCount > lastAlumniCount) {
                        const newAlumniCount = currentAlumniCount - lastAlumniCount;
                        showBrowserNotification(
                            '🎓 Pendaftaran Alumni Baru!',
                            `Ada ${newAlumniCount} pendaftaran alumni baru yang perlu diverifikasi.`
                        );
                    }
                }

                // =============================
                // 🔢 UPDATE BADGE TOTAL
                // =============================
                if (currentTotal > 0) {
                    if (!badge) {
                        const bellIcon = document.querySelector('.bx-bell');
                        let span = document.createElement('span');
                        span.id = "notif-count";
                        span.className = "badge bg-danger rounded-pill badge-notifications";
                        span.innerText = currentTotal;
                        bellIcon.parentElement.appendChild(span);
                    } else {
                        badge.innerText = currentTotal;
                    }
                } else {
                    if (badge) badge.remove();
                }

                // =============================
                // 👥 UPDATE TAB ALUMNI
                // =============================
                let alumniContainer = document.getElementById('alumni-list');
                if (alumniContainer && data.alumni_html) {
                    alumniContainer.innerHTML = data.alumni_html;
                }

                // Update badge alumni tab
                let alumniTab = document.getElementById('alumni-tab');
                if (alumniTab) {
                    let existingBadge = alumniTab.querySelector('.badge');

                    if (currentAlumniCount > 0) {
                        if (existingBadge) {
                            existingBadge.textContent = currentAlumniCount;
                        } else {
                            let badge = document.createElement('span');
                            badge.className = 'badge bg-warning ms-1';
                            badge.textContent = currentAlumniCount;
                            alumniTab.appendChild(badge);
                        }
                    } else {
                        if (existingBadge) {
                            existingBadge.remove();
                        }
                    }
                }

                // =============================
                // 📩 UPDATE TAB INBOX
                // =============================
                let inboxContainer = document.getElementById('inbox-list');
                if (inboxContainer && data.inbox_html) {
                    inboxContainer.innerHTML = data.inbox_html;
                }

                // Update badge inbox tab
                let inboxTab = document.getElementById('inbox-tab');
                if (inboxTab) {
                    let inboxCount = data.inbox_count || 0;
                    let existingBadge = inboxTab.querySelector('.badge');

                    if (inboxCount > 0) {
                        if (existingBadge) {
                            existingBadge.textContent = inboxCount;
                        } else {
                            let badge = document.createElement('span');
                            badge.className = 'badge bg-primary ms-1';
                            badge.textContent = inboxCount;
                            inboxTab.appendChild(badge);
                        }
                    } else {
                        if (existingBadge) {
                            existingBadge.remove();
                        }
                    }
                }

                // Update last counts
                lastTotal = currentTotal;
                lastAlumniCount = currentAlumniCount;
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            })
            .finally(() => {
                isLoading = false;
            });
    }

    // =============================
    // 🚀 INITIALIZATION
    // =============================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize notification sound
        try {
            notificationSound = initNotificationSound();
        } catch (e) {
            console.log('Sound initialization failed:', e);
        }

        // Request notification permission
        requestNotificationPermission();

        // Load notifications immediately
        loadNotifications();

        // Auto-refresh every 5 seconds (real-time feel)
        setInterval(loadNotifications, 5000);

        // Refresh when tab becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                loadNotifications();
            }
        });

        // Add click event to bell icon for manual refresh
        const bellIcon = document.querySelector('.bx-bell');
        if (bellIcon) {
            bellIcon.addEventListener('click', function() {
                loadNotifications();
            });
        }
    });

    // =============================
    // 📊 CONSOLE INFO (for debugging)
    // =============================
    console.log('🔔 Real-time Notification System Initialized');
    console.log('⏱️  Auto-refresh: Every 5 seconds');
    console.log('🔊 Sound: ' + (notificationSound ? 'Enabled' : 'Disabled'));
    console.log('🖥️  Browser Notification: ' + (Notification.permission === 'granted' ? 'Enabled' : 'Disabled'));
</script>
