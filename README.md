# PULSE — Paljaya Ultimate Service Ecosystem

Enterprise Digital Workplace untuk **Perumda Paljaya**: satu ruang kerja virtual untuk
seluruh division, department, employee, process, workflow, document, reporting,
dashboard, dan AI services.

**Status**: Stage 1 — Foundation ✅ · Berikutnya: Stage 2 — PULSE Core
(lihat [docs/roadmap.md](docs/roadmap.md))

## Teknologi

Laravel 12 · PHP ^8.2 · MySQL · Blade · Tailwind CSS 4 · Alpine.js · Vite

> Catatan: Laravel 13 sudah rilis tetapi mensyaratkan PHP ^8.3. Karena stack yang
> ditetapkan adalah PHP 8.2 (Laragon), PULSE memakai Laravel 12. Upgrade ke Laravel 13
> mudah dilakukan begitu PHP di Laragon dinaikkan ke 8.3+.

## Arsitektur Singkat

**Modular monolith** — satu aplikasi Laravel, kode terorganisasi dalam modul dengan
boundary tegas:

```
modules/
└── Core/        ← PULSE Core (foundation semua modul)
                   [berikutnya: Budget/, IT/, Procurement/, ...]
```

- Modul aktif didaftarkan eksplisit di `config/modules.php`.
- Setiap modul punya provider, routes, views, migrations, tests sendiri
  (konvensi: [docs/modules/creating-a-module.md](docs/modules/creating-a-module.md)).
- AI tidak pernah mengakses database langsung — hanya lewat service layer dengan
  permission user ([ADR-005](docs/architecture/decisions/ADR-005-ai-access-boundary.md)).

Dokumen lengkap: [docs/architecture/overview.md](docs/architecture/overview.md) ·
ADR di [docs/architecture/decisions/](docs/architecture/decisions/) ·
Keputusan yang masih terbuka: [docs/open-questions.md](docs/open-questions.md)

## Setup Development (Laragon)

```bash
# 1. Clone ke folder Laragon (mis. C:\laragon\www\pulse) lalu:
composer install
copy .env.example .env          # Linux/Mac: cp .env.example .env
php artisan key:generate

# 2. Buat database MySQL bernama `pulse` (HeidiSQL/phpMyAdmin), lalu:
php artisan migrate --seed
# Seeder mengisi struktur organisasi resmi + akun admin awal:
#   admin@paljaya.local / password  (override via PULSE_ADMIN_EMAIL / PULSE_ADMIN_PASSWORD;
#   WAJIB diganti di environment non-lokal)

# 3. Frontend:
npm install
npm run build                   # atau `npm run dev` saat development

# 4. Jalankan (atau pakai virtual host Laragon pulse.test):
php artisan serve
```

## Testing

```bash
php artisan test
```

Test modul berada di `modules/<Name>/tests/` (testsuite "Modules" di `phpunit.xml`)
dan berjalan dengan SQLite in-memory — tidak menyentuh database MySQL development.
