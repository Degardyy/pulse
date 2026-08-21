# PULSE — Open Questions (Butuh Keputusan Sebelum Stage Terkait)

Prinsip: requirement yang belum jelas **tidak ditebak**. Setiap butir di bawah memblokir
stage tertentu; Foundation (Stage 1) tidak terblokir oleh butir mana pun.

> Format: konteks → opsi → rekomendasi arsitek. Keputusan final dicatat sebagai ADR.

## 1. Sumber Autentikasi — *memblokir Stage 2 (Authentication)*

Bagaimana pegawai login?

- **A. Akun lokal PULSE** (email/username + password, dikelola admin PULSE) — paling
  sederhana, tanpa dependensi eksternal.
- **B. Integrasi direktori existing** (Active Directory/LDAP/Google Workspace/M365 SSO) —
  bila Paljaya sudah punya identitas terpusat.
- **C. Hybrid** — lokal sekarang, SSO menyusul (dirancang agar kolom identitas siap).

**Rekomendasi**: C — mulai lokal, abstraksi login disiapkan agar SSO bisa ditambah tanpa
migrasi ulang. Perlu konfirmasi: apakah Paljaya memiliki AD/SSO saat ini?

## 2. Struktur Organisasi Resmi — *memblokir Stage 2 (Organization)*

Butuh SK struktur organisasi terbaru: daftar lengkap Division, Department, dan Position,
serta apakah ada level di antaranya (Sub-department? Seksi?). Juga: apakah struktur sering
berubah sehingga perlu *effective date* (riwayat struktur)?

**Rekomendasi**: model hirarki dengan effective date sejak awal bila reorganisasi pernah
terjadi dalam 3 tahun terakhir; bila tidak, model sederhana dulu.

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
