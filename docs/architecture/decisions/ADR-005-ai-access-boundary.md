# ADR-005: AI Access Boundary — Service Layer, Bukan Database

**Status**: Accepted · **Tanggal**: 2026-08-21 · **Stage**: Foundation (implementasi pada stage AI Integration)

## Konteks

PULSE akan memiliki AI services (assistant, data/document analysis, summarization, report
generation, recommendation, knowledge management, predictive analytics). Risiko terbesar:
AI membaca data melampaui hak akses user, atau memutasi data tanpa jejak.

## Keputusan

1. AI **tidak pernah** memegang koneksi database, menjalankan SQL, atau membaca model
   secara langsung.
2. Semua kemampuan AI melewati **Core AI Foundation (gateway)** yang:
   - berjalan **atas nama user yang sedang login** (impersonation context, bukan system user);
   - mengeksekusi hanya **service layer method** yang terdaftar sebagai *AI-callable tool*
     (allowlist eksplisit, bukan refleksi otomatis);
   - menjalankan **Policy/permission check yang sama** dengan jalur HTTP sebelum setiap call;
   - menulis **audit trail** untuk setiap tool call AI (user, tool, parameter, hasil ringkas);
   - menerapkan aksi mutasi hanya melalui workflow/approval yang berlaku — AI boleh
     **mengusulkan** (draft), manusia yang **menyetujui**.
3. Setiap modul menyatakan sendiri service mana yang AI-callable beserta deskripsi
   parameternya (AI-ready by design), sehingga penambahan kemampuan AI tidak butuh
   perubahan pada gateway.

## Alasan

**Teknis**
- Reuse total: otorisasi, validasi, dan business rule sudah ada di service layer — AI
  mewarisinya gratis; tidak ada jalur bypass yang harus diamankan terpisah.
- Allowlist + audit membuat perilaku AI dapat diverifikasi dan di-forensik.

**Bisnis**
- Kepatuhan dan kepercayaan: jawaban AI dijamin hanya berisi data yang memang boleh
  dilihat user penanya; keputusan tetap di tangan manusia.

## Konsekuensi

- Service layer harus disiplin: semua business logic lewat service (bukan di controller),
  karena service adalah kontrak untuk HTTP, API, **dan** AI sekaligus.
- Pemilihan provider LLM dan deployment-nya adalah keputusan terpisah
  (lihat `docs/open-questions.md`).
