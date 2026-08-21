# PULSE — Open Questions (Butuh Keputusan Sebelum Stage Terkait)

Prinsip: requirement yang belum jelas **tidak ditebak**. Setiap butir di bawah memblokir
stage tertentu; Foundation (Stage 1) tidak terblokir oleh butir mana pun.

> Format: konteks → opsi → rekomendasi arsitek. Keputusan final dicatat sebagai ADR.

## 1. Sumber Autentikasi — ✅ DIJAWAB (2026-08-21)

**Keputusan: C (Hybrid)** — akun lokal sekarang, SSO menyusul. Diimplementasikan pada
Stage 2 Iterasi 1; lihat [ADR-006](architecture/decisions/ADR-006-hybrid-authentication.md).
Pertanyaan lanjutan (belum mendesak): provider SSO mana yang dipakai Paljaya kelak.

## 2. Struktur Organisasi Resmi — ✅ DIJAWAB (2026-08-21)

Dokumen resmi "Struktur Organisasi (Bhs Inggris), 1 Juli 2026" diterima dan di-seed:
3 direktorat, 11 division + unit Internal Audit, 34 department — lihat
[core-organization.md](modules/core-organization.md). Effective-date history ditunda
sampai reorganisasi nyata pertama (struktur saat ini di-soft-disable via `is_active`).
Pertanyaan lanjutan untuk iterasi Employee: daftar Position di bawah level Department
Head (staff level) dan data pegawai non-kepala.

**Lanjutan (dijawab 2026-08-21)**: otoritas pengelolaan akun = **Department IT saja**
(role User Administrator; ADR-007). Human Capital tidak mengelola akun.

## 3. Sumber Data Pegawai — *memblokir Stage 2 (Employee)*

- **A. Input manual di PULSE** (PULSE = master data pegawai).
- **B. Import/sinkron dari sistem HR/payroll existing** (PULSE = konsumen).

**Rekomendasi**: tergantung ada/tidaknya sistem HR. Bila ada, B (hindari dua master).

## 4. Kebijakan Approval — *memblokir Stage 2 (Workflow)*

Apakah rantai approval mengikuti hirarki jabatan otomatis (atasan langsung → kepala
department → kepala division) atau ditetapkan per jenis dokumen (mis. budget revision
butuh Direktur Keuangan)? Adakah batas nominal yang mengubah rantai (mis. > Rp X naik
satu level)?

**Rekomendasi**: workflow engine berbasis konfigurasi per document type + amount threshold
(fleksibel untuk semua kasus di atas), tetapi butuh contoh SOP approval nyata untuk validasi.

## 5. Struktur Anggaran & Kode Rekening — *memblokir Stage 8 (Budget Engine)*

- Bagaimana chart of account / kode rekening anggaran Paljaya (mengikuti standar BUMD/
  Pergub tertentu?)
- Periode anggaran: tahunan Jan–Des saja, atau juga multi-year (RUPM/investasi)?
- Apakah revisi anggaran mengubah alokasi in-place dengan versi, atau membuat dokumen
  anggaran baru?
- Sumber realisasi: input manual, atau interface dari sistem akuntansi existing?

**Rekomendasi**: minta RKA/laporan realisasi contoh 1 department sebagai acuan ERD.

## 6. Bahasa Antarmuka

- **A. Bahasa Indonesia saja** · **B. English saja** · **C. Bilingual (id default, en tersedia)**

**Rekomendasi**: A untuk kecepatan, tetapi semua string lewat lang files sejak awal
(i18n-ready) sehingga C murah bila dibutuhkan.

## 7. AI Provider & Data Residency — *memblokir Stage 9*

- Provider LLM mana (API cloud seperti Anthropic Claude, atau model on-prem)?
- Adakah kebijakan yang melarang data tertentu keluar infrastruktur Paljaya?
- Anggaran berlangganan API?

**Rekomendasi**: gateway dirancang provider-agnostic (ADR-005); keputusan provider bisa
ditunda sampai Stage 9 tanpa memengaruhi arsitektur.

## 8. Modul Pertama — *memblokir Stage 5*

Rekomendasi arsitek: **Helpdesk** (dependensi minimal, dipakai semua pegawai → adopsi
platform cepat, melatih pola workflow+notification sebelum modul berisiko tinggi seperti
Budget). Alternatif: IT Asset. Mohon konfirmasi prioritas bisnis.

## 9. Deployment Production — *memblokir Stage 6+*

Laragon adalah environment development. Untuk production: server on-prem Paljaya? VPS?
Spesifikasi? Kebijakan backup dan TLS? (Menentukan deployment consideration setiap modul.)
