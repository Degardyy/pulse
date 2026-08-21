# ADR-007: RBAC — Permission Deklaratif per Modul, Grant Role Ber-scope

**Status**: Accepted · **Tanggal**: 2026-08-21 · **Stage**: 2 — PULSE Core (Iterasi 3)

## Konteks

PULSE membutuhkan otorisasi yang: (a) granular per modul, (b) ber-scope organisasi
(user hanya bertindak sesuai Division/Department-nya — requirement inti Budget Engine),
(c) auditable. Opsi package `spatie/laravel-permission` dievaluasi: matang, tetapi model
scope-nya (single `team_id`) tidak memetakan hirarki dua-level Division→Department kita.

## Keputusan

RBAC ringan milik sendiri (konsisten ADR-002), dengan tiga prinsip:

1. **Permission dideklarasikan di kode, bukan di UI.** Setiap modul mendeklarasikan
   permission-nya di service provider (`$permissions = ['core.users.manage' => ...]`),
   terkumpul di `PermissionRegistry` (singleton), dan di-mirror ke DB oleh
   `pulse:sync-permissions` (dipanggil seeder & deploy). Kode = source of truth;
   permission yang tak lagi dideklarasikan otomatis di-prune.
2. **Role adalah data; grant role membawa scope.** `core_role_user` punya
   `division_id`/`department_id` nullable:
   - keduanya null → grant **global**;
   - `division_id` terisi → berlaku untuk division itu **dan seluruh department-nya**;
   - `department_id` terisi → berlaku untuk department itu saja.
   **Least privilege**: pemeriksaan tanpa konteks scope hanya dipenuhi grant global —
   otoritas ber-scope tidak pernah bocor ke fungsi global.
3. **Satu titik keputusan.** `AccessService::allows(user, code, scope)` dipakai
   `User::hasPermission()`, `Gate::before` (sehingga `can()`/`@can` bekerja untuk semua
   kode permission), dan kelak AI gateway (ADR-005) — tidak ada jalur cek kedua.

Role bawaan (system): **Administrator** (`is_super`, bypass semua cek) dan
**User Administrator** (kelola akun). Keputusan pemilik produk 2026-08-21:
**otoritas administrasi akun dipegang Department IT** — staf IT diberi role
User Administrator; Human Capital tidak mengelola akun.

## Alasan

**Teknis**: permission ikut modul-nya (modul baru membawa permission-nya sendiri tanpa
menyentuh Core); scope dua-level native terhadap model organisasi kita; `is_super` di
role (bukan hard-coded user) tetap dapat dicabut. **Bisnis**: pemetaan langsung ke
kebutuhan "user hanya melihat/bertindak sesuai role, permission, division, department".

## Konsekuensi

- UI User Management saat ini mengelola grant **global**; grant ber-scope dikelola modul
  masing-masing saat modul ber-scope hadir (Division Portal, Budget) — mekanisme sudah
  teruji sekarang.
- Role CRUD UI belum ada (role bawaan via seeder); ditambahkan saat kebutuhan role
  kustom nyata muncul.
- Setiap fitur baru wajib: deklarasi permission di provider modul + Policy yang
  memanggil `hasPermission()`.
