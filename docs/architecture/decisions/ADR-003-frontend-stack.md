# ADR-003: Frontend — Blade + Tailwind CSS + Alpine.js, Server-Rendered

**Status**: Accepted · **Tanggal**: 2026-08-21 · **Stage**: Foundation

## Konteks

Stack yang ditetapkan: Blade, Tailwind CSS, Alpine.js. Yang perlu diputuskan adalah pola
pemakaiannya dan identitas visual.

## Keputusan

1. **Server-rendered Blade** sebagai default untuk semua halaman; Alpine.js hanya untuk
   interaktivitas lokal (dropdown, modal, tabs, form dinamis). Tidak ada SPA framework.
2. **Design tokens Paljaya** didefinisikan sekali sebagai Tailwind theme
   (`resources/css/app.css`) dan dipakai seluruh modul:
   - `paljaya-500 #006EB6` (Paljaya Blue — warna aksi/brand utama)
   - `navy #163A5F` (permukaan gelap: sidebar, header tabel)
   - `deep #002060` (heading/emphasis)
   - skala turunan 50–900 untuk state hover/muted.
3. **Layout enterprise SaaS** disediakan Core (`<x-core::layouts.app>`): sidebar navigasi
   per-scope user, topbar, content area — modul hanya mengisi konten, tidak membuat layout
   sendiri.

## Alasan

**Teknis**
- Server-rendered + RBAC menyatu alami: view hanya merender apa yang boleh dilihat user;
  tidak ada duplikasi logika otorisasi di client.
- Satu sumber design token = konsistensi antar modul otomatis (DRY), rebranding murah.

**Bisnis**
- Kurva belajar rendah untuk tim; kecepatan delivery modul tinggi.
- Identitas Paljaya Blue konsisten di seluruh workplace — terasa satu produk, bukan
  kumpulan aplikasi.

## Konsekuensi

- Kebutuhan interaksi sangat kompleks di masa depan (mis. grafik interaktif berat)
  diselesaikan per-kasus (chart library ringan), bukan dengan mengganti stack.
