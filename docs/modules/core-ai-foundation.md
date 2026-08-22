# Core — AI Foundation Scaffold (Stage 2, Iterasi 9)

## 1–2. Objective & Business Requirement

Fondasi AI sesuai ADR-005: AI **tidak pernah** menyentuh database — hanya boleh
memanggil kemampuan yang di-allowlist eksplisit, **atas nama user**, dengan
otorisasi user, dan setiap panggilan ter-audit. Iterasi ini membangun gateway +
registry + tools nyata; **provider LLM belum dipasang** (open question #7 —
keputusan provider/data residency).

## 3. Architecture

```
[LLM provider — Stage 9, plug-in di atas]
        │  hanya menghasilkan tool call
        ▼
AiGateway::call(user, tool, args)
  1. tool ada di AiToolRegistry?  (allowlist eksplisit, bukan refleksi)
  2. user berwenang?              (permission via AccessService)
  3. audit 'ai_tool_call'         (aktor eksplisit + tool + args)
  4. eksekusi handler             (selalu lewat service layer ber-scope)
```

`AiToolRegistry::schemaFor(user)` menghasilkan daftar tool yang BOLEH dilihat
LLM untuk user itu — tool ber-permission tidak pernah muncul di schema user
yang tidak berwenang.

## 4–5. Data Model & Migration

Tidak ada tabel baru; event audit baru `ai_tool_call` (tampil di viewer audit).

## 6–7. Tools Core terdaftar (contoh hidup)

`organization.summary` · `documents.search` (via `visibleTo` — AI mewarisi
lingkup dokumen user) · `approvals.pending` (workflow user) · `audit.recent`
(permission `core.audit.view`).

## 8–11. Authorization & Validation

Tool tak dikenal → InvalidArgumentException; tanpa izin → AuthorizationException;
argumen diteruskan ke handler yang memvalidasi/meng-scope sendiri.

## 12. Testing

`AiFoundationTest` — 6 test: tolak tool tak dikenal, hasil + audit ber-aktor,
**documents.search menghormati scope** (Indriany menemukan, Alvin tidak),
tool ber-permission ditolak/diterima sesuai role, schema terfilter per user.
Suite total 86. (Test menemukan & memperbaiki: audit kini menerima aktor
eksplisit — gateway bisa dipanggil di luar sesi HTTP, mis. dari job.)

## 13–15. Security / Integration / Deployment

Empat lapis: allowlist → permission → scope service → audit. Modul baru
mendaftarkan tool AI-nya di provider masing-masing (pola registry yang sama).
Tanpa konfigurasi deployment; provider LLM kelak = konfigurasi + adapter yang
mengubah jawaban LLM menjadi `AiGateway::call` — tidak menyentuh lapisan ini.

## 16. Status

Selesai (scaffold). Stage 9 menyusul: provider LLM, percakapan di panel PULSE AI,
tool tulis ber-workflow (AI mengusulkan, manusia menyetujui — ADR-005).
