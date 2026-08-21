# Core — Authentication (Stage 2, Iterasi 1)

## 1. Objective

Pegawai Paljaya dapat masuk ke PULSE secara aman dengan akun lokal; arsitektur siap
ditautkan ke SSO tanpa migrasi ulang (ADR-006).

## 2. Business Requirement

- Login email+password; tanpa self-registration (akun dibuat administrator).
- Akun nonaktif tidak boleh bisa login (pegawai keluar/di-suspend).
- Brute-force protection.
- Siap SSO di masa depan (keputusan pemilik produk: hybrid).

## 3. Architecture

`LoginController` (tipis) → `LoginRequest` (validasi + rate limit) →
`AuthenticationService::attemptLocal()` (satu-satunya pintu autentikasi) → session guard
`web`. Logout menginvalidasi session + regenerate CSRF token.

## 4. Data Model

Tabel `users` (bawaan Laravel) + kolom: `auth_provider` (default `local`),
`provider_id` (nullable, unik per provider), `is_active` (default true),
`last_login_at`; `password` menjadi nullable (akun SSO). Model: `Modules\Core\Models\User`.

## 5. Migration

`2026_08_21_100001_add_auth_fields_to_users_table` — aditif + `change()` pada password;
`down()` tersedia.

## 6–7. Model & Controller/Service

- `Modules\Core\Models\User` (fillable/casts baru, `PROVIDER_LOCAL`).
- `Modules\Core\Services\Auth\AuthenticationService` — attemptLocal/logout/onLoggedIn.
- `Auth\LoginController` (create/store/destroy), `DashboardController`.

## 8. Authorization

Middleware `guest` (halaman login) dan `auth` (dashboard, organisasi). Redirect user
terautentikasi → dashboard (`bootstrap/app.php`). RBAC penuh menyusul pada iterasi
Role & Permission.

## 9. Route

`GET /login` (`login`), `POST /login` (`login.store`), `POST /logout` (`logout`),
`GET /dashboard` (`core.dashboard`).

## 10. UI/UX

Halaman login kartu terpusat gaya enterprise Paljaya Blue; layout menampilkan navigasi +
menu user (Alpine dropdown) saat login, tombol "Masuk" saat guest.

## 11. Validation

`LoginRequest`: email wajib+format, password wajib; pesan Bahasa Indonesia.

## 12. Testing

`modules/Core/tests/Feature/AuthenticationTest.php` — 9 test: halaman login, login sukses
(+`last_login_at` terisi), password salah, akun nonaktif, rate limit setelah 5 gagal,
logout, redirect guest dari halaman terlindungi, redirect user dari /login, sapaan dashboard.

## 13. Security Consideration

Password bcrypt (cast `hashed`); rate limit 5/menit per email+IP; session regenerate saat
login (fixation) dan invalidate saat logout; pesan error generik (tidak membocorkan
keberadaan akun); akun nonaktif ditolak di level kredensial; tanpa self-registration.

## 14. Integration Consideration

Semua modul memakai guard `web` + `auth()->user()` yang sama. SSO kelak masuk lewat
`AuthenticationService` (ADR-006). AI gateway kelak berjalan atas identitas user ini
(ADR-005).

## 15. Deployment Consideration

Seeder membuat akun admin awal (`PULSE_ADMIN_EMAIL`/`PULSE_ADMIN_PASSWORD`, default
`admin@paljaya.local`/`password`) — **wajib diganti di environment non-lokal**. Produksi:
paksa HTTPS agar session cookie aman.

## 16. Status Dokumentasi

Selesai untuk iterasi ini. Belum tersedia (terjadwal): password reset via email, forced
password change, manajemen user oleh admin (iterasi User Management).
