# ADR-006: Autentikasi Hybrid — Lokal Sekarang, SSO-Ready

**Status**: Accepted (keputusan pemilik produk, 2026-08-21) · **Stage**: 2 — PULSE Core

## Konteks

Open question #1 dijawab: **opsi C (hybrid)** — akun lokal PULSE sekarang, integrasi SSO
(AD/LDAP/OAuth) dapat ditambahkan kemudian tanpa migrasi ulang.

## Keputusan

1. **Skema user SSO-ready sejak awal**:
   - `auth_provider` (default `local`) + `provider_id` (subject dari IdP, nullable,
     unik per provider) mengidentifikasi sumber identitas;
   - `password` nullable — akun SSO tidak pernah menyimpan password;
   - `is_active` — menonaktifkan akun tanpa menghapus (pegawai keluar), diperiksa
     saat login;
   - `last_login_at` — jejak pemakaian.
2. **Satu pintu autentikasi**: `Modules\Core\Services\Auth\AuthenticationService`.
   Login lokal memakai `attemptLocal()`; callback SSO kelak menambah method sendiri dan
   memakai session handling + gate `is_active` + bookkeeping yang sama.
3. **Kebijakan akun**: tidak ada self-registration — akun dibuat administrator
   (enterprise workplace, bukan aplikasi publik). Login di-rate-limit 5 percobaan
   per email+IP per menit.
4. Model `User` berada di `Modules\Core\Models` (Core memiliki User Management),
   bukan `App\Models`.

## Alasan

**Teknis**: kolom identitas provider ditambahkan sekarang seharga satu migration;
menambahkannya belakangan berarti migrasi data user yang sudah hidup. Service tunggal
mencegah dua jalur login dengan aturan berbeda (DRY, Security by Design).

**Bisnis**: PULSE bisa diluncurkan tanpa menunggu keputusan infrastruktur SSO; saat
Paljaya siap ber-SSO, akun existing tinggal ditautkan (`auth_provider`+`provider_id`).

## Konsekuensi

- Password reset via email belum tersedia (butuh konfigurasi mail server) — admin
  me-reset manual; dijadwalkan pada iterasi User Management.
- Saat SSO diaktifkan kelak: keputusan provider (AD/Azure/Google) dicatat sebagai
  ADR baru; akun lokal tetap didukung untuk akun darurat/servis.
