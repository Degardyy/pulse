# ADR-009: Workflow Engine — Approval Berbasis Konfigurasi, Approver Dibekukan per Instance

**Status**: Accepted · **Tanggal**: 2026-08-23 · **Stage**: 2 — PULSE Core (Iterasi 7)

## Konteks

Semua proses PULSE (dokumen, kelak budget/helpdesk/procurement) butuh persetujuan
berjenjang yang aturannya berbeda-beda dan akan berubah. SOP resmi belum diterima
(open question #4) — engine harus generik dan aturan konkret harus berupa data.

## Keputusan

1. **Definisi = data**: `workflow_definitions` + `workflow_steps` berurutan; jenis
   approver: `position` (kode posisi), `department_head`/`division_head` (dari unit
   pemohon), `role` (kode role global). Threshold nominal ditambahkan saat modul
   bernominal (Budget) hadir.
2. **Approver dibekukan saat instance dibuat**: setiap langkah template di-resolve
   menjadi `workflow_instance_steps` dengan position_id/role_id konkret. Eligibility
   memeriksa kursi/role yang DIPEGANG SAAT INI atas langkah beku itu — pergantian
   pejabat tidak merusak approval berjalan, dan "menunggu saya" tetap query-able.
3. **Engine tidak tahu subjeknya** (morph). Modul bereaksi lewat event
   `WorkflowApproved`/`WorkflowRejected` — sesuai aturan boundary (integrasi via
   event), engine tidak mengimpor Document/Budget.
4. **Use case pertama (ASUMSI, menunggu SOP)**: publikasi dokumen seluruh Paljaya —
   siapa pun yang punya unit boleh MEMINTA; **Division Head Corporate Secretary**
   menyetujui; pemegang `core.documents.publish-org` terbit langsung tanpa workflow.
   Dokumen pending hanya terbaca pengunggah + approver; ditolak hanya pengunggah.
5. Keputusan final (instance selesai tidak bisa diputuskan ulang); setiap langkah
   menyimpan aktor, waktu, catatan; semuanya ter-audit (Auditable) dan semua pihak
   ter-notifikasi (Notifier).

## Alasan

Aturan-sebagai-data = SOP berubah tanpa deploy; pembekuan approver menjawab
pertanyaan audit "siapa berwenang saat itu"; event = modul baru menambah proses
approval tanpa menyentuh engine.

## Konsekuensi

- UI admin definisi workflow menyusul (saat ini via seeder — konsisten: aturan
  datang dari SOP tertulis).
- Fitur lanjutan disengaja belum ada: delegasi, paralel/quorum, eskalasi timeout,
  cancel oleh pemohon — ditambahkan saat kebutuhan nyata muncul.
