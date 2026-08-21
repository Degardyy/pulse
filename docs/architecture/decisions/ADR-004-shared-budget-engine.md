# ADR-004: Budget Engine sebagai Satu Modul Bersama

**Status**: Accepted · **Tanggal**: 2026-08-21 · **Stage**: Foundation (implementasi menyusul)

## Konteks

Setiap department membutuhkan budget monitoring. Anti-pattern yang ingin dihindari:
setiap department membangun modul budget sendiri-sendiri (duplikasi logika, angka tidak
bisa dikonsolidasikan, standar approval berbeda-beda).

## Keputusan

Satu modul **`modules/Budget`** — Common Enterprise Budget Engine — dipakai seluruh
division/department. Department tidak memiliki tabel/logic budget sendiri; mereka memiliki
**data budget ber-scope** di dalam engine yang sama.

Cakupan engine: Planning, Allocation, Revision, Commitment, Realization, Monitoring,
Forecast, Approval (via Core Workflow), Transfer, Closing, Reporting.

Model data (garis besar; ERD detail dibuat pada stage implementasi Budget):

- Dimensi: Division, Department, Program, Activity, Budget Account, Budget Period.
- Fakta: Allocation, Commitment, Realization — semua mengacu ke dimensi di atas dan dapat
  dilampiri Document (Core).
- Hirarki roll-up: Transaction → Activity → Program → Department → Division → Executive.

## Alasan

**Teknis**
- Konsolidasi otomatis Department → Division → Executive hanya mungkin bila semua angka
  hidup dalam satu skema dengan dimensi yang seragam.
- Satu implementasi approval/validasi/closing = satu tempat perbaikan bug (DRY).
- AI budget analysis (forecast, anomaly, underutilization) butuh data seragam lintas
  department — mustahil bila tiap department punya struktur sendiri.

**Bisnis**
- Angka executive dashboard dijamin konsisten dengan angka department (single source of truth).
- Department baru mendapat budget monitoring tanpa development baru — cukup konfigurasi
  scope + chart of account.

## Konsekuensi

- Kebutuhan khusus per department dipenuhi lewat **konfigurasi** (jenis account, struktur
  program/activity, aturan approval), bukan lewat fork kode. Bila ada kebutuhan yang benar-
  benar tidak muat, diputuskan lewat ADR baru.
- Modul division (IT, Procurement) menampilkan "Budget Monitoring"-nya sebagai view dari
  Budget Engine ber-scope department masing-masing.
