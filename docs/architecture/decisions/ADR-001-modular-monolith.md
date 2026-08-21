# ADR-001: Modular Monolith sebagai Gaya Arsitektur

**Status**: Accepted · **Tanggal**: 2026-08-21 · **Stage**: Foundation

## Konteks

PULSE harus melayani banyak division/department dengan modul yang berbeda-beda, dibangun
bertahap oleh tim kecil, dan di-deploy di infrastruktur sederhana (Laragon saat development,
server on-prem saat production). Modul-modul saling terhubung erat: budget dipakai semua
department, workflow/notification/document dipakai semua modul.

## Keputusan

Satu aplikasi Laravel (satu codebase, satu database, satu deployment) dengan kode
diorganisasi dalam **modules** yang memiliki boundary tegas (lihat ADR-002).

## Alasan

**Teknis**
- Transaksi lintas modul (mis. approval budget → notification → audit) tetap ACID dalam
  satu database; tidak perlu distributed transaction/saga.
- Satu autentikasi, satu session, satu deployment pipeline — kompleksitas operasional minimal.
- Boundary modul (namespace, provider, service contract) memberi manfaat separation of
  concerns tanpa biaya jaringan/infra microservices.

**Bisnis**
- Tim kecil dapat menguasai seluruh sistem; onboarding developer lebih cepat.
- Time-to-value per modul lebih cepat karena tidak ada overhead infra per service.
- Jalur evolusi tetap terbuka: modul yang kelak butuh skala independen dapat diekstrak
  karena komunikasinya sudah melalui interface, bukan query langsung.

## Konsekuensi

- Disiplin boundary harus dijaga lewat konvensi + code review (tidak ada enforcement
  runtime seperti pada microservices).
- Satu deployment berarti regresi di satu modul dapat memengaruhi lainnya → wajib ada
  automated test per modul sebelum merge.
