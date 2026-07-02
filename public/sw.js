/**
 * Service Worker untuk Web Push Notification
 * arief-arsitek.com
 */

self.addEventListener("push", function (event) {
    if (!event.data) return;

    let data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: "Notifikasi Baru", body: event.data.text() };
    }

    const title = data.title || "📬 Pesan Baru Masuk!";
    const options = {
        body: data.body || "Ada pesan baru di inbox.",
        icon: data.icon || "/assets/img/favicon/favicon-96x96.png",
        badge: data.badge || "/assets/img/favicon/favicon-32x32.png",
        data: data.data || {},
        actions: data.actions || [
            { action: "view_inbox", title: "Lihat Inbox" },
            { action: "close", title: "Tutup" },
        ],
        requireInteraction: false,
        vibrate: [200, 100, 200],
        tag: "inbox-notification", // satu notif per grup, tidak menumpuk
        renotify: true,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    if (event.action === "close") return;

    // Buka URL inbox saat notifikasi diklik
    const url =
        event.notification.data && event.notification.data.url
            ? event.notification.data.url
            : "/inbox";

    event.waitUntil(
        clients
            .matchAll({ type: "window", includeUncontrolled: true })
            .then(function (clientList) {
                // Kalau tab admin sudah terbuka, fokus ke sana
                for (let client of clientList) {
                    if (client.url.includes("/inbox") && "focus" in client) {
                        return client.focus();
                    }
                }
                // Buka tab baru kalau belum ada
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            }),
    );
});

self.addEventListener("pushsubscriptionchange", function (event) {
    // Re-subscribe otomatis kalau subscription expired
    event.waitUntil(
        self.registration.pushManager.subscribe({ userVisibleOnly: true }),
    );
});
