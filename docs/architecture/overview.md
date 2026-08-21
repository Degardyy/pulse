# PULSE — Architecture Overview

**PULSE (Paljaya Ultimate Service Ecosystem)** adalah Enterprise Digital Workplace untuk
Perumda Paljaya: satu ruang kerja virtual untuk seluruh division, department, employee,
process, workflow, document, reporting, dashboard, dan AI services.

## 1. Gaya Arsitektur: Modular Monolith

PULSE dibangun sebagai **modular monolith**: satu aplikasi Laravel, satu database,
satu deployment — tetapi kode diorganisasi ke dalam **modules** dengan boundary yang tegas.

Alasan (lihat [ADR-001](decisions/ADR-001-modular-monolith.md)):

- **Bisnis**: satu tim kecil, satu server (Laragon → on-prem), kebutuhan integrasi antar
  fungsi yang tinggi. Microservices menambah biaya operasional tanpa manfaat pada skala ini.
- **Teknis**: boundary modul memberi maintainability dan jalur evolusi — modul yang matang
  dapat diekstrak menjadi service terpisah di masa depan tanpa menulis ulang.

## 2. Lapisan Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│  Presentation   Blade + Tailwind CSS + Alpine.js            │
│                 (Division Portals, Dashboards, Forms)       │
├─────────────────────────────────────────────────────────────┤
│  HTTP Layer     Controllers · Form Requests · Middleware    │
│                 Policies (authorization)                    │
├─────────────────────────────────────────────────────────────┤
│  Service Layer  Business logic per module                   │
│                 ← SATU-SATUNYA pintu masuk untuk AI         │
├─────────────────────────────────────────────────────────────┤
│  Domain/Data    Eloquent Models · Query Scopes · Events     │
├─────────────────────────────────────────────────────────────┤
│  Database       MySQL (satu schema, prefix per module)      │
└─────────────────────────────────────────────────────────────┘
```

Aturan dependensi antar modul:

1. **Setiap modul boleh bergantung pada `Core`. `Core` tidak boleh bergantung pada modul lain.**
2. Modul division/department tidak boleh saling memanggil secara langsung; integrasi
   antar modul dilakukan melalui **service contract (interface)** atau **event** yang
   dideklarasikan di Core.
3. Tidak ada query lintas modul terhadap tabel modul lain — gunakan service milik modul tersebut.

## 3. Peta Modul

```
modules/
├── Core/                  ← PULSE CORE (foundation, Stage 2)
│   │                        Authentication · User · Employee · Organization
│   │                        Division · Department · Position · Role · Permission
│   │                        Notification · Workflow · Document · Audit Trail
│   │                        Dashboard · Reporting · AI Foundation
├── Budget/                ← Common Enterprise Budget Engine (shared, dipakai semua
│   │                        division/department — BUKAN per-department)
├── IT/                    ← Division Portal: Information Technology
│   │                        Helpdesk · IT Asset · Monitoring · dst.
├── Procurement/           ← Division Portal: Procurement
│   │                        RUP · Vendor · Contract · P3DN · dst.
└── ...                    ← Division berikutnya, bertahap
```

Setiap modul mengikuti konvensi yang sama (lihat [creating-a-module.md](../modules/creating-a-module.md)):
service provider sendiri, routes sendiri, migrations sendiri, views sendiri, tests sendiri,
dan **table prefix** sendiri (`core_`, `budget_`, `it_`, `proc_`).

## 4. PULSE Core

Core adalah foundation yang dipakai semua modul. Modul lain **mengonsumsi** kemampuan Core
(auth, RBAC, workflow, notification, document, audit) dan tidak mengimplementasikan
ulang kemampuan tersebut.

Kemampuan kunci Core:

- **RBAC + scope organisasi**: permission diperiksa bersama konteks Division/Department.
  User hanya melihat dan bertindak sesuai Role, Permission, Division, dan Department-nya.
- **Workflow engine**: definisi approval berbasis konfigurasi (bukan hard-code),
  dipakai oleh Budget, Procurement, Helpdesk, dll.
- **Audit trail**: semua mutasi data penting tercatat (who, what, when, before/after).
- **Document service**: penyimpanan dokumen ber-metadata, terhubung ke entity mana pun.
- **AI Foundation**: gateway ber-otorisasi untuk semua fitur AI (lihat §6).

## 5. Budget Engine (Shared Module)

Budget Monitoring dirancang sebagai **satu Common Enterprise Budget Engine**
(`modules/Budget`), bukan modul terpisah per department
(lihat [ADR-004](decisions/ADR-004-shared-budget-engine.md)).

- Hirarki: Executive → Division → Department → Program → Activity → Transaction.
- Konsolidasi otomatis bottom-up: realisasi transaksi ter-roll-up ke Activity → Program →
  Department → Division → Executive Dashboard.
- Setiap department memakai engine yang sama dengan scope data masing-masing (RBAC + scope).
- Lifecycle: Planning → Allocation → (Revision/Transfer) → Commitment → Realization →
  Monitoring/Forecast → Closing → Reporting, dengan approval melalui Core Workflow.

## 6. AI Boundary

**AI tidak pernah mengakses database secara langsung.**

```
AI Assistant / Analysis / Forecast
        │  (tool calls / structured requests)
        ▼
Core AI Foundation (gateway)
        │  1. resolve user context (role, permission, division, department)
        │  2. authorize setiap aksi seperti user biasa (Policies)
        │  3. audit trail setiap akses AI
        ▼
Module Service Layer  ──►  Models  ──►  MySQL
```

AI hanya boleh memanggil **service layer** yang sama dengan yang dipakai controller,
dengan identitas dan permission user yang sedang login. Dengan begitu AI tidak pernah
melihat data yang tidak boleh dilihat user tersebut. Detail di
[ADR-005](decisions/ADR-005-ai-access-boundary.md).

## 7. Prinsip Non-Negotiable

SOLID · DRY · KISS · Separation of Concerns · RBAC · Policy-based authorization ·
Auditability · Security by Design · API-ready (service layer dapat diekspos sebagai REST API
tanpa refactor) · AI-ready (setiap service dirancang agar dapat dipanggil AI gateway).

## 8. Status Pembangunan

Pembangunan bertahap — lihat [roadmap.md](../roadmap.md). Stage saat ini: **Foundation**.
