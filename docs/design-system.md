# PULSE Design System — Panduan Praktis

Filosofi: **Calm Enterprise** (ADR-008). Halaman menyusun primitives — bukan membuat
UI sendiri. Referensi hidup: halaman Beranda (`core::home`).

## Tokens (resources/css/app.css)

| Peran | Utility | Light | Dark |
|---|---|---|---|
| Page background | `bg-bg` | `#f6f7f9` | `#0d1220` |
| Content plane | `bg-surface` | `#ffffff` | `#151b2c` |
| Hover/subtle fill | `bg-surface-2` | `#eff1f5` | `#1d2437` |
| Teks utama | `text-ink` | `#17233a` | `#e8edf6` |
| Teks sekunder | `text-ink-2` | — | — |
| Teks muted/label | `text-ink-3` | — | — |
| Hairline | `ring-line` / `border-line` | `#e6e9ef` | `#232c42` |
| Aksi/brand | `bg-accent`, `text-accent`, `bg-accent-soft` | Paljaya `#006eb6` | `#4aa3e0` |
| Status | `success / warning / danger` + `-soft` | subtle | subtle |

Aturan: **tidak ada hex mentah dan tidak ada kelas `dark:` di halaman** — dark mode
bekerja otomatis lewat token. (Pengecualian sadar: panel brand login yang memang
selalu gelap.)

## Tipografi

Inter Variable (self-host). Utilitas: `text-display` (judul halaman),
`text-title` (judul panel), `text-label` (eyebrow uppercase — alat pengelompokan
utama pengganti kotak). Body 13.5px.

## Hierarki tanpa kotak

Urutan alat pengelompokan: (1) whitespace + `text-label`, (2) `divide-y divide-line`,
(3) `ring-1 ring-line` panel — hanya bila benar-benar perlu bidang. Shadow hanya
untuk lapisan mengambang.

## Komponen

```
<x-core::ui.panel>            permukaan konten (padding opsional)
<x-core::ui.page-header>      judul + deskripsi + slot actions
<x-core::ui.status tone="">   pill status (success/warning/danger/accent/neutral)
<x-core::ui.progress value=""> bar tipis
<x-core::ui.avatar name="">   inisial (sm/md/lg)
<x-core::ui.empty-state>      keadaan kosong
<x-core::ui.icon name="">     icon set internal (stroke 1.7)
<x-core::shell.*>             sidebar, topbar, command-palette, notifications,
                              ai-panel, bottom-nav — dirakit layouts/app
```

Layout: `<x-core::layouts.app :title="" :breadcrumbs="[]">` (shell penuh) dan
`<x-core::layouts.guest>` (halaman publik).

## Navigasi

Satu sumber: `Modules\Core\Services\NavigationService`. Modul baru menambah item di
sana → otomatis muncul di sidebar, command palette (Ctrl+K), dan bottom nav mobile.
Item ber-permission difilter per user.

## Interaksi

- Motion 150–250ms, `--ease-out-soft`; jangan animasikan dekorasi.
- Focus state: utility `focusable` (outline accent 2px) — wajib pada semua kontrol.
- Shortcut: `Ctrl/Cmd+K` palette, `Esc` menutup lapisan.
- Skala ikon: 18px dalam navigasi/baris, 16px dalam tombol kecil.

## Checklist kualitas sebelum halaman dianggap selesai

Terlihat seperti produk enterprise modern? Hierarki langsung terbaca? Yang butuh
perhatian menonjol? Aksi jelas? Tenang (tidak penuh kotak/warna)? Konsisten token +
primitives? Bekerja di dark mode dan mobile? Fokus keyboard terlihat?
