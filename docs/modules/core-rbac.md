# Core — Role & Permission + User Management (Stage 2, Iterasi 3)

## 1. Objective

Otorisasi granular ber-scope organisasi (ADR-007) dan administrasi akun oleh Department
IT (keputusan pemilik produk) — prasyarat semua fitur tulis PULSE.

## 2. Business Requirement

- User hanya melihat/bertindak sesuai Role, Permission, Division, Department.
- Akun dikelola **hanya oleh Department IT** (buat akun, tautkan pegawai, atur role,
  reset password, aktif/nonaktif). Human Capital tidak mengelola akun.
- Password sementara dibuat sistem, ditampilkan sekali, tidak pernah disimpan plain.

## 3. Architecture

Kode → `PermissionRegistry` (deklarasi per modul) → `PermissionSync`/DB;
keputusan akses terpusat di `AccessService` (semantik scope: global ⊃ division ⊃
department; cek tanpa scope hanya dipenuhi grant global); terhubung ke Laravel lewat
`Gate::before` sehingga `can()`, `@can`, dan Policy bekerja normal.
`UserManagementService` menangani mutasi akun; `UserPolicy` menjaga setiap endpoint.

## 4. Data Model

```
core_permissions (code unik, module)  ←M:N→  core_roles (is_system, is_super)
core_role_user: user_id, role_id, division_id?, department_id?   ← scope grant
```

## 5. Migration

`100005_create_core_rbac_tables` — 4 tabel; pivot cascade, scope FK restrict.

## 6–7. Model & Service/Controller

`Permission`, `Role` (konstanta code); `User::roles()` (withPivot scope),
`User::hasPermission()`; `Access\{PermissionRegistry, AccessService, PermissionSync}`;
`UserManagementService` (create/update/resetPassword/toggleActive; sinkronisasi role
**hanya menyentuh grant global** — grant ber-scope milik modul lain tak tersentuh);
`Admin\UserController`; command `pulse:sync-permissions`.

## 8. Authorization

`UserPolicy`: viewAny→`core.users.view`; create/update/resetPassword/toggle→
`core.users.manage`; menonaktifkan akun sendiri selalu ditolak. FormRequest juga
memeriksa policy sebelum validasi (403, bukan error field). Role bawaan:
Administrator (super), User Administrator (untuk staf IT).

## 9. Route

`/admin/users` (index/create/store/{user}/edit/update/reset-password/toggle-active),
semua di balik `auth` + policy.

## 10. UI/UX

Halaman Pengguna (tabel: pegawai tertaut, role chip, status, login terakhir; aksi per
baris), form buat/ubah (role checkbox ber-deskripsi, select pegawai yang belum tertaut),
banner amber sekali-tampil untuk password sementara. Nav "Pengguna" hanya muncul bila
punya izin.

## 11. Validation

`StoreUserRequest`/`UpdateUserRequest`: email unik, pegawai belum tertaut akun lain
(ignore diri sendiri saat edit), role harus ada. Pesan Bahasa Indonesia.

## 12. Testing

17 test baru — `RbacTest` (8): sync idempoten + prune, super bypass, permission role,
tanpa role = tanpa izin, scope division mencakup department-nya saja, scope department
sempit, cek unscoped tak dipenuhi grant scoped, ability tak dikenal fall-through.
`UserManagementTest` (9): 403 non-IT, guest redirect, list, create (+hash password
sesuai flash, link pegawai, role), validasi duplikat, update role, **edit akun tidak
menghapus grant ber-scope**, reset password, larangan nonaktif diri sendiri.

## 13. Security Consideration

Password `Str::password(12)` + bcrypt, tampil sekali via flash; least-privilege scope;
`is_super` hanya lewat role system; self-lockout dicegah di policy; 403 sebelum
validasi. Audit trail mutasi akun menyusul di iterasi Audit Trail (dicatat sebagai
dependensi).

## 14. Integration Consideration

Modul mana pun tinggal: deklarasi `$permissions` di provider + Policy ber-`hasPermission`.
Scope grant dipakai Division Portal & Budget Engine (cek `hasPermission(code, $dept)`).
AI gateway (ADR-005) memakai `AccessService` yang sama.

## 15. Deployment Consideration

Setiap deploy yang mengubah deklarasi permission: `php artisan pulse:sync-permissions`
(atau `db:seed`). Grant role admin pertama via seeder.

## 16. Status Dokumentasi

Selesai. Menyusul: role CRUD UI (bila perlu role kustom), audit trail mutasi akun,
forced password change saat login pertama.
