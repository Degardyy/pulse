# Core — Workflow (Stage 2, Iterasi 7)

## 1–2. Objective & Business Requirement

Approval berjenjang berbasis konfigurasi (ADR-009). SOP resmi belum diterima —
definisi pertama memakai asumsi terdokumentasi: publikasi dokumen org-wide
disetujui Corporate Secretary.

## 3. Architecture

`WorkflowService` (start/approve/reject/pendingFor/requestsOf/isApproverFor);
approver dibekukan per instance (position_id/role_id); hasil dipublikasikan lewat
event; `HandleDocumentPublishDecision` = glue Document. Lihat ADR-009.

## 4–5. Data Model & Migration

`core_workflow_definitions` → `core_workflow_steps` (template);
`core_workflow_instances` (subjek morph, status) → `core_workflow_instance_steps`
(approver beku, aktor, catatan). `100009` + `100010` (status dokumen).

## 6–7. Model & Service/Controller

4 model workflow (Auditable); `ApprovalController` (index/approve/reject);
`DocumentService` bercabang: butuh approval → pending + start workflow;
disetujui → publish + notifikasi audiens; ditolak → status rejected.

## 8. Authorization

Eligibility diperiksa di engine (403 bila bukan approver langkah aktif atau
instance sudah diputuskan). Dokumen pending: pengunggah/manage/approver aktif.

## 9. Route

`GET /approvals` · `POST /approvals/{instance}/approve|reject` (note opsional ≤500).

## 10. UI/UX

Halaman Persetujuan: kartu permintaan (subjek, definisi, pemohon, langkah,
tautan tinjau dokumen) dengan aksi Setujui/Tolak + catatan; riwayat "Permintaan
saya" ber-status. Item perhatian "N persetujuan menunggu Anda" di Beranda kini
**data hidup**. Nav "Persetujuan" via NavigationService. Status dokumen
(Menunggu persetujuan/Ditolak) tampil di daftar dokumen pengunggah.

## 11. Validation

note ≤500; keputusan ganda ditolak; definisi nonaktif tidak bisa dipakai.

## 12. Testing

`WorkflowTest` — 7 test: start+resolve approver, pending tersembunyi tapi
reviewable approver + notifikasi, approve→publish+notifikasi semua pihak,
reject→note sampai ke pemohon, non-approver 403 + keputusan final,
multi-langkah berurutan (division_head → role), publisher langsung lewati
workflow. Plus DocumentTest disesuaikan. Total suite 76.

## 13–15. Security / Integration / Deployment

Semua keputusan ter-audit dengan aktor+waktu+catatan; approver tidak bisa
memutus dua kali; subjek pending tidak bocor (scope+policy). Integrasi modul
baru: definisikan workflow (seeder/SK), panggil `start()`, dengarkan event.
Deployment: `db:seed` memuat definisi.

## 16. Status

Selesai dengan asumsi ADR-009 §4 — **mohon konfirmasi SOP** (open question #4).
Menyusul: UI admin definisi, threshold nominal, delegasi/eskalasi/cancel.
