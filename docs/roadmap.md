# PULSE — Development Roadmap

Pembangunan dilakukan **bertahap**: satu stage dan satu modul pada satu waktu.
Setiap stage selesai = teruji + terdokumentasi, baru lanjut.

## Stage 1 — Foundation ✅ (stage ini)

- [x] Laravel skeleton (PHP ^8.2, MySQL, Vite)
- [x] Module system: `modules/`, `config/modules.php`, `ModuleServiceProvider` (ADR-002)
- [x] Skeleton `modules/Core` (provider, routes, views, tests)
- [x] Design tokens Paljaya Blue + base layout + landing page (ADR-003)
- [x] Dokumen arsitektur, ADR-001…005, open questions
- [x] Foundation tests hijau

## Stage 2 — PULSE Core (berjalan)

Urutan sub-modul Core (masing-masing satu iterasi kecil):

1. ✅ Authentication — hybrid-ready (ADR-006), login/logout, rate limit, tests
2. ✅ Organization — Directorate → Division → Department, seeded struktur resmi
   1 Juli 2026 (Position menyusul bersama Employee)
3. ✅ Employee & Position — 50 posisi struktural, 40 pejabat (7 Plt, 5 vacant) dari
   bagan resmi; assignment ber-riwayat; link `users.employee_id`. (Pegawai staff dan
   CRUD/User Management admin menyusul setelah RBAC.)
4. ✅ Role & Permission (ADR-007) — permission deklaratif per modul, grant role
   ber-scope Division/Department, `AccessService` + Gate; User Management oleh
   Department IT (list/create/edit, role, reset password, aktif/nonaktif)
5. Audit Trail (otomatis untuk semua mutasi penting)
6. Notification (in-app; channel lain menyusul)
7. Document (upload, metadata, attach ke entity mana pun)
8. Workflow (definisi approval berbasis konfigurasi)
9. Dashboard & Reporting foundation (widget framework)
10. AI Foundation *scaffold* (gateway + allowlist registry, tanpa provider dulu)

> Open questions #1 dan #2 sudah dijawab (lihat `open-questions.md`). Prasyarat iterasi
> Workflow: open question #4 (contoh SOP approval).

## Stage 3 — One Division: Information Technology & Procurement

Division Portal shell: navigasi per-division, dashboard division kosong yang siap diisi
widget modul.

## Stage 4 — One Department: Information Technology

Department dashboard + navigasi modul department.

## Stage 5 — One Module (modul pertama department IT)

Kandidat pertama: **Helpdesk** (nilai cepat, dependensi Core minimal: auth, RBAC,
notification, workflow sederhana) — konfirmasi di open questions #8.

## Stage 6 — Testing

Hardening modul pertama: feature tests menyeluruh, permission matrix test, UAT checklist.

## Stage 7 — Refinement

Perbaikan hasil UAT, polish UI/UX, dokumentasi user.

## Stage 8 — Integration

- Budget Engine (`modules/Budget`, ADR-004) — karena dipakai lintas department,
  dibangun pada stage integrasi, dimulai dari satu department pilot.
- Integrasi antar modul via service contract & event.

## Stage 9 — AI Integration

- Implementasi AI Foundation penuh (ADR-005): provider, tool registry, audit.
- Use case pertama yang disarankan: summarization & report drafting (read-only, risiko
  terendah), kemudian budget analysis/forecast.

## Definition of Done per Modul

Objective & business requirement → architecture → ERD → migration plan → model → service →
policy → route → UI → validation → testing → security & integration consideration →
deployment note → dokumentasi. (16 butir checklist — lihat template di
`docs/modules/creating-a-module.md`.)
