# ADR-002: Module System — Konvensi Sendiri, Bukan Package Pihak Ketiga

**Status**: Accepted · **Tanggal**: 2026-08-21 · **Stage**: Foundation

## Konteks

Modular monolith membutuhkan mekanisme modul. Opsi yang dievaluasi:

1. **`nwidart/laravel-modules`** — package populer, banyak fitur (generator, aktivasi dinamis).
2. **Konvensi sendiri** — direktori `modules/`, PSR-4 `Modules\`, satu service provider per
   modul, registrasi eksplisit via `config/modules.php`.

## Keputusan

Opsi 2: **konvensi sendiri** yang ringan.

- `modules/<Name>/` berisi `Providers/`, `Http/`, `Models/`, `Services/`, `Policies/`,
  `routes/`, `resources/views/`, `database/migrations/`, `tests/`.
- `config/modules.php` mendaftarkan modul yang aktif (eksplisit, dapat dinonaktifkan).
- `App\Providers\ModuleServiceProvider` me-register provider setiap modul aktif;
  provider modul memuat routes/migrations/views/translations miliknya sendiri.

## Alasan

**Teknis**
- KISS: kebutuhan kita (namespace terpisah + loading per modul) tercapai dengan ±100 baris
  kode yang kita kuasai penuh, tanpa dependency yang harus diikuti siklus rilisnya.
- Upgrade Laravel tidak pernah terhambat kompatibilitas package modul.
- Registrasi eksplisit di config = tidak ada magic scanning; mudah di-debug, modul dapat
  dimatikan per environment.

**Bisnis**
- Mengurangi risiko jangka panjang (abandonware) untuk fondasi paling kritikal platform.
- Konvensi terdokumentasi di `docs/modules/creating-a-module.md` sehingga developer baru
  dapat membuat modul konsisten tanpa mempelajari package eksternal.

## Konsekuensi

- Tidak ada artisan generator modul bawaan — template modul didokumentasikan; generator
  sederhana dapat ditambahkan kemudian bila diperlukan.
- Kita bertanggung jawab atas kualitas loader sendiri → dicakup unit test foundation.
