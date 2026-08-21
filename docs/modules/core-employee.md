# Core — Employee & Position (Stage 2, Iterasi 2)

## 1. Objective

Pejabat struktural Perumda Paljaya menjadi master data: siapa memegang jabatan apa,
di unit mana, definitif atau Plt, dan posisi mana yang vacant — fondasi untuk User
Management, RBAC scoping, dan approval workflow.

## 2. Business Requirement

Scope iterasi ini (keputusan pemilik produk): **pejabat struktural dari bagan resmi
1 Juli 2026 saja**; pegawai staff menyusul saat datanya tersedia.

Fakta bagan yang harus terwakili:
- 50 posisi struktural: 1 President Director, 2 Director, 11 Division Head,
  2 posisi unit Internal Audit (Head + Secretary), 34 Department Head.
- Satu orang bisa memegang dua jabatan (Dede Sudewa: ITP Plt + HCGA; Wenang Adam:
  GRC&HSE Plt + LGA; Sri Wahyuni, Ismet, Bella Nasila D. serupa) → 40 pegawai unik.
- 7 penugasan berstatus **Plt (acting)**; 5 posisi **vacant** (BDEV, MEM, PROC,
  CFIN, HSE).

## 3. Architecture

Position = kursi struktural yang menempel pada tepat satu unit organisasi;
Employee = orang; PositionAssignment = relasi M:N ber-riwayat (`ended_at` null =
sedang menjabat, terisi = riwayat). Vacant = posisi tanpa assignment aktif.
Read model via `EmployeeService`; `OrganizationService::structureTree()` kini
eager-load pejabat per unit.

## 4. Data Model (ERD logis)

```
core_positions (code unik, level, FK nullable → directorate/division/department)
core_employees (name, employee_number nullable, email nullable)
core_position_assignments (position_id, employee_id, is_acting, started_at, ended_at)
users.employee_id (nullable, unique) → core_employees
```

Level: president_director, director, division_head, department_head, unit_head,
unit_secretary, (staff — disiapkan untuk masa depan).

## 5. Migration

`100003_create_core_employee_tables` (3 tabel, index posisi/pegawai aktif),
`100004_add_employee_id_to_users_table` (link user↔employee 1:1, restrictOnDelete).

## 6–7. Model & Service/Controller

`Position` (konstanta level, `currentAssignment`, `isVacant()`), `Employee`
(`activeAssignments`, `user`), `PositionAssignment`; `EmployeeService`
(`listWithPositions()`, `counts()`); `EmployeeController@index`.
`OfficialsSeeder` idempoten **dan merekonsiliasi**: assignment aktif yang tidak lagi
sesuai bagan di-akhiri (`ended_at`), bukan dihapus — pergantian pejabat meninggalkan
riwayat.

## 8. Authorization

Halaman pegawai: semua user terautentikasi (data jabatan struktural bersifat internal
umum). Mutasi belum diekspos; CRUD menyusul bersama RBAC.

## 9. Route

`GET /employees` (`core.employees.index`), middleware `auth`.

## 10. UI/UX

Komponen `<x-core::holder>` (nama + badge Plt amber / badge Vacant abu) dipakai di
halaman organisasi (pejabat per direktorat/division/department) dan tabel pegawai
(kursi sebagai chip). Dashboard: kartu "Pejabat Struktural" + jumlah vacant.

## 11. Validation

Read-only (seeder satu-satunya jalur tulis; constraint DB: unique code, FK restrict,
unique users.employee_id). Form validation menyusul bersama CRUD.

## 12. Testing

`EmployeeTest` — 7 test: integritas seeder (50/40/45/7/5 + daftar vacant persis),
dua jabatan satu orang (Dede Sudewa), idempotensi + rekonsiliasi pergantian pejabat,
link user↔employee, halaman pegawai, pejabat tampil di halaman organisasi, guard auth.

## 13. Security Consideration

Di balik `auth`; tanpa input user; penghapusan pegawai/posisi yang masih dirujuk
ditolak DB (restrict). Data pribadi minimal (nama jabatan publik internal; NIP/email
nullable, belum diisi).

## 14. Integration Consideration

- `users.employee_id` = jembatan account ↔ pegawai; dipakai iterasi User Management
  dan RBAC (scope division/department diturunkan dari posisi aktif pegawai).
- Approval workflow kelak me-resolve approver dari Position (mis. "Division Head
  FIN"), bukan dari nama orang — pergantian pejabat tidak mengubah konfigurasi.
- Assignment ber-riwayat = fondasi audit "siapa menjabat saat dokumen X disetujui".

## 15. Deployment Consideration

`php artisan db:seed` aman diulang. Pergantian pejabat via SK baru: perbarui
`OfficialsSeeder` (atau kelak UI admin); assignment lama otomatis diakhiri, riwayat
tersimpan.

## 16. Status Dokumentasi

Selesai untuk iterasi ini. Menyusul: pegawai staff (butuh data), CRUD admin ber-RBAC,
User Management (pembuatan akun tertaut pegawai), employee_number/email saat tersedia.
