# Core — Organization (Stage 2, Iterasi 1)

## 1. Objective

Struktur organisasi resmi Perumda Paljaya (per 1 Juli 2026) menjadi master data di PULSE:
fondasi scoping RBAC, employee assignment, division portal, dan konsolidasi budget.

## 2. Business Requirement

Sumber: dokumen resmi "Struktur Organisasi (Bhs Inggris), 1 Juli 2026" — diverifikasi
per-kotak dari koordinat PDF:

- 3 direktorat: President Director; Technical & Commercial; Administration & Finance.
- 11 division + 1 unit (Internal Audit langsung di bawah President Director, tanpa department).
- 34 department. Catatan penting hasil verifikasi: **Monitoring & Evaluation berada di
  bawah Corporate Strategy** (bukan Engineering & PMO); Laboratory di bawah GRC & HSE.
- Tidak dimodelkan sebagai unit organisasi: Supervisory Board (organ pengawas) dan
  President Director Experts (advisory). Pemegang jabatan (nama-nama) = data Employee,
  di-seed pada iterasi Employee.

## 3. Architecture

Read model melalui `OrganizationService` (`structureTree()`, `counts()`) — controller,
modul lain, dan AI gateway tidak boleh query tabel `core_*` langsung (boundary rule).
CRUD admin menyusul setelah RBAC ada (saat ini struktur dikelola via seeder — sumber
resmi memang dokumen SK).

## 4. Data Model (ERD logis)

```
core_directorates 1──N core_divisions 1──N core_departments
```

Semua entity: `code` (unik, stabil untuk integrasi), `name`, `sort_order` (urutan bagan),
`is_active` (soft-disable saat reorganisasi). `core_divisions.type`: `division` | `unit`.

## 5. Migration

`2026_08_21_100002_create_core_organization_tables` — tiga tabel, FK `restrictOnDelete`
(struktur tidak boleh terhapus diam-diam).

## 6–7. Model & Service/Controller

`Directorate`, `Division`, `Department` (relasi ber-`sort_order`);
`OrganizationService`; `OrganizationController@index`.
`OrganizationSeeder` idempoten (updateOrCreate berbasis `code`) — aman dijalankan ulang.

## 8. Authorization

Halaman struktur: semua user terautentikasi (informasi internal umum). Mutasi belum
diekspos. Scoping per division/department diberlakukan pada modul-modul berikutnya.

## 9. Route

`GET /organization` (`core.organization.index`), middleware `auth`.

## 10. UI/UX

Per direktorat: section ber-header navy; kartu division (badge tipe+kode) berisi daftar
department; ringkasan jumlah di atas.

## 11. Validation

Iterasi ini read-only (seeder = satu-satunya jalur tulis; constraint DB menjaga integritas:
unique code, FK). Form validation menyusul bersama CRUD admin.

## 12. Testing

`OrganizationTest` — 4 test: seeder menghasilkan struktur resmi (jumlah + spot check
MONEV→Corporate Strategy, ITP→AFD, IA=unit, OPM 6 department), idempoten, filter
`is_active` pada tree & counts, halaman organisasi tampil untuk user terautentikasi.

## 13. Security Consideration

Di balik `auth`; tidak ada input user; FK restrict mencegah penghapusan berantai.

## 14. Integration Consideration

`division_id`/`department_id` akan direferensikan oleh: Employee (penempatan), RBAC
(scope), Budget Engine (dimensi konsolidasi), Division Portal (navigasi). `code` stabil —
jangan diubah tanpa ADR.

## 15. Deployment Consideration

`php artisan db:seed` aman diulang (idempoten). Reorganisasi di masa depan: perubahan
struktur = perubahan seeder (dokumen SK baru) + `is_active=false` untuk unit lama;
kebutuhan riwayat effective-date dievaluasi saat reorganisasi nyata pertama.

## 16. Status Dokumentasi

Selesai untuk iterasi ini. Menyusul: Position & Employee (nama pejabat dari bagan),
CRUD admin ber-RBAC, effective-date history bila dibutuhkan.
