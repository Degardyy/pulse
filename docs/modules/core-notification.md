# Core — Notification (Stage 2, Iterasi 5)

## 1. Objective

Notification center in-app: pengguna melihat hal yang butuh perhatiannya (akun,
akses, dokumen, kelak approval/tiket) tanpa membuka tiap modul.

## 2. Business Requirement

Notifikasi per pengguna, badge belum-dibaca, tandai dibaca (satuan/semua), klik
mengikuti tautan aksinya. Channel lain (email/WhatsApp) menyusul bila diputuskan.

## 3. Architecture

Laravel database notifications (framework core, bukan package pihak ketiga) dengan
**satu bentuk notifikasi** `PulseNotification` (title, body, url, tone) dan **satu
pintu pengirim** `Notifier::send()` — modul tidak menyentuh kelas notifikasi
langsung; channel baru cukup ditambah di satu tempat.

## 4–5. Data Model & Migration

Tabel `notifications` standar Laravel (uuid, type, notifiable morph, data JSON,
read_at) — `100007_create_notifications_table`.

## 6–7. Model & Service/Controller

`PulseNotification`, `Notifier`; `NotificationController` (index, read →
markAsRead + follow url, readAll). Trigger nyata saat ini: akun dibuat (selamat
datang), role diubah (hanya bila benar-benar berubah), password di-reset (tone
warning). Iterasi Document menambah "dokumen dibagikan".

## 8. Authorization

Semua route di balik `auth`; pengguna hanya bisa membaca notifikasinya sendiri
(query di-scope `auth()->user()->notifications()` → 404 untuk milik orang lain).

## 9. Route

`GET /notifications` · `POST /notifications/read-all` · `POST /notifications/{id}/read`.

## 10. UI/UX

Popover bell di topbar: dot accent saat ada yang belum dibaca, 8 terbaru, unread
di-highlight halus, "Tandai semua dibaca", tautan ke halaman penuh ber-pagination.
Waktu relatif berbahasa Indonesia.

## 11. Validation

Tidak ada input bebas (payload dibuat sistem); id divalidasi lewat scoped findOrFail.

## 12. Testing

`NotificationTest` — 9 test: pengiriman + payload, trigger welcome/role/reset,
anti-noise (role tak berubah → tanpa notifikasi), read+redirect, isolasi antar
pengguna (404), read-all, halaman, badge shell.

## 13. Security Consideration

Isolasi per pengguna; URL aksi dibuat sistem (bukan input user); mutasi via POST+CSRF.

## 14. Integration Consideration

Modul mana pun: `app(Notifier::class)->send($users, ...)`. Workflow/Helpdesk/Budget
memakai pintu yang sama; kelak channel email tinggal ditambah di `PulseNotification::via()`.

## 15. Deployment Consideration

Channel database — tanpa konfigurasi tambahan. Pertumbuhan tabel kecil; pembersihan
notifikasi lama (mis. > 6 bulan yang sudah dibaca) bisa dijadwalkan kelak.

## 16. Status Dokumentasi

Selesai. Menyusul: preferensi notifikasi per pengguna, channel email, real-time
(polling/websocket) bila dibutuhkan.
