# ADR-008: PULSE Design Language — "Calm Enterprise"

**Status**: Accepted · **Tanggal**: 2026-08-23 · **Stage**: UI Phase 1 (Design System + Application Shell)

## Konteks

PULSE harus terasa seperti produk enterprise modern (kelas Linear/Notion/Slack), bukan
dashboard administrasi pemerintahan yang kaku. Kebutuhan: identitas Paljaya Blue
dipertahankan tetapi tidak mendominasi, skala ke 20+ department tanpa jadi ramai,
dark/light mode, dan aksesibilitas.

## Keputusan

1. **Filosofi "Calm Enterprise"** — kompleksitas dikomunikasikan tanpa terlihat
   kompleks: tipografi sebagai mekanisme hierarki utama (eyebrow label +
   display/title scale), pengelompokan lewat whitespace dan hairline — bukan
   kotak-kotak; border minimal (`ring-1 ring-line`), bayangan hanya untuk lapisan
   mengambang (popover/dialog); biru hanya untuk aksi, state aktif, dan aksen penting.
2. **Semantic design tokens** (`resources/css/app.css`): seluruh warna produk mengalir
   melalui token peran — `bg / surface / surface-2 / ink / ink-2 / ink-3 / line /
   accent / success / warning / danger` (+ varian `-soft`). Komponen tidak pernah
   menyebut hex. Dark mode = redefinisi token di `.dark` (class-driven, resolusi
   preferensi sebelum first paint) — tanpa satu pun kelas `dark:` di markup halaman.
3. **Application shell** sebagai satu komponen sistem: sidebar ringan collapsible
   (state persist), topbar dengan breadcrumb + pencarian global, **command palette
   Ctrl+K** (sumber navigasi yang sama dengan sidebar via `NavigationService`),
   notification center, entry point AI first-class (panel samping, bukan floating
   button), bottom navigation di mobile.
4. **Typography**: Inter Variable **self-hosted** (di-bundle Vite) — tanpa CDN font;
   utilitas `text-display`, `text-title`, `text-label` menstandarkan skala.
5. **Motion standard**: 150–250ms, ease-out (`--ease-out-soft`); transisi untuk
   state dan hierarki, bukan dekorasi.
6. **Komponen reusable** di `core::` namespace: `ui.*` primitives (panel, page-header,
   status, progress, avatar, empty-state, icon set stroke internal) dan `shell.*`
   (sidebar, topbar, command-palette, notifications, ai-panel, bottom-nav).
   Halaman menyusun primitives — bukan membuat UI one-off.

## Alasan

**Teknis**: token semantik membuat dark mode dan rebranding murah; satu sumber
navigasi berarti modul baru otomatis muncul di sidebar, palette, dan mobile;
self-host font menghapus dependensi jaringan eksternal (relevan untuk on-prem).
**Bisnis**: PULSE harus dipakai setiap hari oleh seluruh pegawai — antarmuka yang
tenang dan premium menaikkan adopsi ("saya datang ke PULSE untuk bekerja").

## Konsekuensi

- Semua UI baru wajib memakai token + primitives; hex mentah atau kelas `dark:`
  di halaman adalah code smell yang ditolak di review.
- Konten Home yang modulnya belum ada (perhatian, tugas, insight AI) adalah
  **fixture demonstrasi** di `DashboardController` — tercatat dan diganti data
  hidup saat modul terkait hadir (Workflow, Helpdesk, Budget, AI Foundation).
- Chart/visualisasi data belum distandarkan — ditetapkan saat Budget Engine
  (fase yang pertama membutuhkannya).
