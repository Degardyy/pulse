# Core — Audit Trail (Stage 2, Iterasi 4)

## 1. Objective

Setiap mutasi data penting dan aktivitas autentikasi tercatat otomatis — who, what,
when, before/after — sebagai fondasi akuntabilitas seluruh modul PULSE (prinsip
Auditability, dan kelak jejak setiap tool call AI per ADR-005).

## 2. Business Requirement

- Pencatatan **otomatis** (tidak bergantung disiplin developer memanggil logger).
- Append-only: log tidak dapat diubah/dihapus dari aplikasi.
- Nilai rahasia (password) tidak pernah tercatat dalam bentuk apa pun (masked).
- Login/logout tercatat dengan IP dan user agent.
- Dapat ditelusuri oleh pihak berwenang (permission), difilter per jenis event.

## 3. Architecture

```
Model (trait Auditable) ─┐
AuthenticationService ───┼──► AuditService (singleton, satu-satunya penulis) ──► core_audit_logs
UserManagementService ───┘         │
                                   └── withoutAuditing(fn) — dipakai seeder
```

- Trait `Auditable` memasang observer created/updated/deleted; per model dapat
  menyetel `$auditExclude` (noise, mis. `last_login_at`) dan `$auditMask` (rahasia).
- Event updated hanya mencatat atribut yang berubah (before/after); bila setelah
  exclude tidak ada perubahan berarti, tidak ada entri (anti-noise).
- Event khusus non-model: `login`, `logout`, `roles_synced` (perubahan role akun
  dicatat sebagai daftar kode role sebelum/sesudah).
- Seeder berjalan dalam `withoutAuditing()` — sumber datanya dokumen SK, bukan aksi user.

## 4. Data Model

`core_audit_logs`: user_id (nullable → "System"), event, auditable morph,
old_values/new_values (JSON), ip_address, user_agent, created_at. **Tanpa
updated_at** — append-only by design. Index: event, created_at, user_id, morph.

## 5. Migration

`100006_create_core_audit_logs_table`.

## 6–7. Model & Service/Controller

`AuditLog` (konstanta event, `subjectLabel()` tahan terhadap record terhapus),
`Concerns\Auditable`, `Audit\AuditService`, `Admin\AuditLogController@index`.
Model ber-audit saat ini: User, Directorate, Division, Department, Employee,
Position, PositionAssignment, Role. **Modul baru cukup `use Auditable;`**.

## 8. Authorization

Permission `core.audit.view` — dipegang Administrator (super) dan User Administrator
(Department IT sebagai operator platform). Halaman diproteksi Gate.

## 9. Route

`GET /admin/audit` (`core.admin.audit.index`), filter `?event=`.

## 10. UI/UX

Filter chip per jenis event; baris: waktu, aktor (System bila null), badge event
ber-tone semantik, subjek; klik baris membuka diff Sebelum/Sesudah + IP/user agent.
Pagination 50/halaman. Masuk sidebar/palette via NavigationService ("Audit Trail").

## 11. Validation

Tidak ada input tulis (read-only viewer); filter event divalidasi implisit
(nilai tak dikenal = hasil kosong).

## 12. Testing

`AuditTest` — 9 test: created/updated(diff)/deleted, masking password +
exclude last_login, login/logout ber-IP, roles_synced via endpoint admin,
withoutAuditing, proteksi permission, halaman + filter.

## 13. Security Consideration

Append-only (tidak ada route mutasi; `UPDATED_AT = null`); password ter-mask di
kedua sisi diff; log menyimpan IP + user agent untuk forensik; akses ber-permission.
Retensi/arsip log jangka panjang: keputusan operasional, dicatat sebagai
pertimbangan deployment.

## 14. Integration Consideration

- Modul berikutnya (Document, Workflow, Budget) otomatis ter-audit dengan
  menambahkan trait — tanpa kode audit khusus.
- AI Foundation (ADR-005) akan menulis event `ai_tool_call` melalui AuditService
  yang sama.
- `roles_synced` melengkapi jawaban "siapa memberi akses apa, kapan".

## 15. Deployment Consideration

Tabel akan tumbuh — pantau ukurannya; kebijakan retensi (mis. arsip > 2 tahun)
ditetapkan bersama GRC saat produksi. Tidak ada konfigurasi tambahan.

## 16. Status Dokumentasi

Selesai. Menyusul: retensi/arsip, filter lanjutan (per user/objek/tanggal),
export CSV bila dibutuhkan GRC.
