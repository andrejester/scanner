{{--
    Web Push Notification Script
    Diinclude di layout admin (app.blade.php)
    Hanya aktif untuk user yang sudah login
--}}
@auth
<script>
(function () {
    'use strict';

    const VAPID_PUBLIC_KEY = '{{ config("webpush.vapid.public_key") }}';
    const SUBSCRIBE_URL    = '{{ route("push.subscribe") }}';
    const UNSUBSCRIBE_URL  = '{{ route("push.unsubscribe") }}';
    const CSRF_TOKEN       = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // =============================
    // Utility: urlBase64ToUint8Array
    // =============================
    function urlBase64ToUint8Array(base64String) {
        const padding  = '='.repeat((4 - base64String.length % 4) % 4);
        const base64   = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData  = window.atob(base64);
        const output   = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; i++) {
            output[i] = rawData.charCodeAt(i);
        }
        return output;
    }

    // =============================
    // Kirim subscription ke server
    // =============================
    function sendSubscriptionToServer(subscription) {
        const key   = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');

        return fetch(SUBSCRIBE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({
                endpoint:    subscription.endpoint,
                keys: {
                    p256dh: key   ? btoa(String.fromCharCode.apply(null, new Uint8Array(key)))   : null,
                    auth:   token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                }
            }),
        });
    }

    // =============================
    // Subscribe ke push
    // =============================
    function subscribePush(registration) {
        return registration.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
        }).then(function (subscription) {
            return sendSubscriptionToServer(subscription);
        });
    }

    // =============================
    // Main: Daftarkan Service Worker & minta izin
    // =============================
    function initWebPush() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('🔕 Web Push tidak didukung browser ini.');
            return;
        }

        navigator.serviceWorker.register('/sw.js')
            .then(function (registration) {
                console.log('✅ Service Worker terdaftar:', registration.scope);

                // Cek permission saat ini
                if (Notification.permission === 'granted') {
                    // Langsung subscribe
                    registration.pushManager.getSubscription().then(function (existing) {
                        if (!existing) {
                            subscribePush(registration).then(function () {
                                console.log('🔔 Web Push subscription berhasil.');
                            });
                        } else {
                            // Kirim ulang ke server (kalau belum tersimpan)
                            sendSubscriptionToServer(existing);
                        }
                    });
                } else if (Notification.permission === 'default') {
                    // Tampilkan tombol izin setelah 3 detik
                    setTimeout(function () {
                        showPermissionPrompt(registration);
                    }, 3000);
                }
            })
            .catch(function (err) {
                console.error('❌ Service Worker gagal:', err);
            });
    }

    // =============================
    // Tampilkan prompt izin notifikasi (UI custom)
    // =============================
    function showPermissionPrompt(registration) {
        // Buat toast prompt
        const toast = document.createElement('div');
        toast.id    = 'push-permission-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 16px 20px;
            max-width: 320px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            animation: slideInUp 0.4s ease;
        `;

        toast.innerHTML = `
            <style>
                @keyframes slideInUp {
                    from { transform: translateY(60px); opacity: 0; }
                    to   { transform: translateY(0);    opacity: 1; }
                }
            </style>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.8rem;">🔔</span>
                <div>
                    <strong style="display:block; font-size:0.9rem;">Aktifkan Notifikasi</strong>
                    <small style="color:#666;">Dapatkan notifikasi langsung saat ada pesan inbox baru masuk.</small>
                </div>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button id="push-deny-btn"
                    style="border:1px solid #ddd; background:#f5f5f5; color:#666; border-radius:6px; padding:6px 14px; cursor:pointer; font-size:0.8rem;">
                    Nanti
                </button>
                <button id="push-allow-btn"
                    style="border:none; background:#696cff; color:#fff; border-radius:6px; padding:6px 14px; cursor:pointer; font-size:0.8rem;">
                    Izinkan
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        // Klik "Izinkan"
        document.getElementById('push-allow-btn').addEventListener('click', function () {
            toast.remove();
            Notification.requestPermission().then(function (permission) {
                if (permission === 'granted') {
                    subscribePush(registration).then(function () {
                        console.log('🔔 Web Push aktif.');
                        showToastNotif('🔔 Notifikasi aktif! Kamu akan mendapat notifikasi saat ada pesan baru.', 'success');
                    });
                }
            });
        });

        // Klik "Nanti" — hilangkan toast
        document.getElementById('push-deny-btn').addEventListener('click', function () {
            toast.remove();
        });

        // Auto hilang setelah 15 detik
        setTimeout(function () {
            if (document.getElementById('push-permission-toast')) {
                toast.remove();
            }
        }, 15000);
    }

    // =============================
    // Toast notif sederhana
    // =============================
    function showToastNotif(message, type) {
        const el = document.createElement('div');
        el.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: #fff;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            animation: slideInUp 0.3s ease;
        `;
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    // Jalankan setelah DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWebPush);
    } else {
        initWebPush();
    }
})();
</script>
@endauth
