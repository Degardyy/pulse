# Core — Dashboard & Reporting Foundation (Stage 2, Iterasi 8)

## 1–2. Objective & Business Requirement

Fondasi agar setiap modul dapat menyumbang **widget dashboard** dan **laporan
ekspor** tanpa menyentuh Core — prasyarat Division Portal (Stage 3) dan
konsolidasi Budget (Stage 8).

## 3. Architecture

Dua registry singleton (pola sama dengan PermissionRegistry/NavigationService —
modul mendaftar di provider-nya, konsumen merender):

- `Dashboard\WidgetRegistry` — widget: key, title, view Blade, sort, `visible(user)`,
  `data(user)` lazy. Beranda merender `forUser()`; dashboard division kelak
  memakai registry yang sama.
- `Reporting\ReportRegistry` — laporan CSV: headers + generator baris lazy
  (`yield`, hemat memori) + permission opsional. `/reports` menampilkan yang
  boleh; unduhan streaming ber-BOM (Excel-friendly).

## 4–5. Data Model & Migration

Tidak ada tabel baru — murni foundation kode.

## 6–7. Registrasi Core (contoh hidup)

`CoreDashboardProvider`: widget **Dokumen Terbaru** (5 dokumen `visibleTo` user —
menghormati lingkup Iterasi 6); laporan **Pejabat Struktural** (semua pengguna)
dan **Audit Trail** (permission `core.audit.view`, `lazy()` cursor).

## 8–9. Authorization & Route

Widget: closure `visible` + data selalu di-scope user. Laporan: permission di
registry, dicek di listing dan unduhan. `GET /reports`, `GET /reports/{key}/download`.

## 10. UI/UX

Widget dirender Beranda sebagai section berlabel (bahasa Calm Enterprise);
halaman Laporan: baris judul+deskripsi+tombol Unduh CSV. Nav "Laporan" via
NavigationService (ikon chart).

## 11–12. Validation & Testing

Key tak dikenal → 404; tanpa izin → 403. `DashboardReportingTest` — 4 test:
widget hanya menampilkan dokumen yang boleh dilihat (Alvin tidak melihat dokumen
IT), listing laporan per permission, isi CSV pejabat nyata (header + baris +
Plt), audit CSV ber-permission + 404 unknown. Suite total 80.

## 13–15. Security / Integration / Deployment

Data widget/laporan selalu melalui service/scope ber-otorisasi — registry tidak
membuka jalur data baru. Modul baru: panggil `register()` di provider-nya.
Tanpa konfigurasi deployment.

## 16. Status

Selesai. Menyusul: filter/parameter laporan (periode), format XLSX/PDF, widget
berukuran/kolom, preferensi susunan widget per pengguna.
