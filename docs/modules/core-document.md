# Core — Document (Stage 2, Iterasi 6)

## 1. Objective

Repositori dokumen ber-lingkup: dokumen unit hanya terbaca unitnya, dokumen
organisasi terbaca seluruh Paljaya — dengan file tersimpan privat dan unduhan
selalu ter-otorisasi.

## 2. Business Requirement (keputusan pemilik produk, 2026-08-23)

- Ada dokumen yang **hanya bisa dibaca department/division**, dan ada yang
  **bisa dibaca seluruh Paljaya**.
- Interpretasi arsitek (terdokumentasi, dapat direvisi):
  - **department** → anggota department + pemegang kursi level division di
    atasnya (kepemimpinan membaca ke bawah);
  - **division** → seluruh anggota division termasuk semua department-nya;
  - **paljaya** → semua pengguna terautentikasi; **mempublikasikannya butuh
    izin** `core.documents.publish-org` (agar tidak semua orang bisa siaran
    organisasi — kelak dipegang Corporate Secretary; saat ini Administrator).
- Unggah ke unit sendiri = hak keanggotaan (tanpa izin khusus); pengunggah
  selalu melihat dan dapat menghapus dokumennya.

## 3. Architecture

Keanggotaan unit diturunkan dari posisi aktif pegawai:
`User::organizationUnitIds()` → `departments` / `divisions` / `division_leads`
(kursi level division, untuk visibilitas ke bawah). Aturan baca hidup di **dua
tempat yang wajib sinkron**: `Document::scopeVisibleTo` (query daftar) dan
`DocumentPolicy::view` (per objek/unduh). `DocumentService` satu-satunya jalur
storage + pemberi notifikasi audiens.

## 4. Data Model

`core_documents`: title, description, category, file_path (disk privat),
file_name/mime/size, visibility (paljaya|division|department), division_id?,
department_id?, uploaded_by. FK restrict; index (visibility, unit).

## 5. Migration

`100008_create_core_documents_table`.

## 6–7. Model & Service/Controller

`Document` (Auditable — unggah/hapus otomatis ter-audit; `visibilityLabel()`,
`sizeLabel()`), `DocumentService` (store+notify audience minus pengunggah,
delete file+record), `DocumentController` (index ber-filter lingkup, create,
store, download streaming, destroy).

## 8. Authorization

`DocumentPolicy`: view (matriks di atas + manage), create (punya unit atau izin),
createWithScope (dipakai FormRequest::authorize → 403 sebelum validasi), delete
(pengunggah/manage). Izin: `core.documents.publish-org`, `core.documents.manage`
(saat ini hanya Administrator; penetapan role fungsional menyusul).

## 9. Route

`GET /documents` (+`?lingkup=unit|paljaya`) · `GET/POST` create/store ·
`GET /documents/{id}/download` · `DELETE /documents/{id}`.

## 10. UI/UX

Daftar baris tenang (judul, lingkup ber-warna, kategori, ukuran, pengunggah,
waktu relatif), filter chip lingkup, form unggah dengan radio lingkup dinamis
(opsi unit mengikuti keanggotaan; Paljaya nonaktif tanpa izin). Nav "Dokumen"
via NavigationService.

## 11. Validation

`StoreDocumentRequest`: judul ≤150, file wajib ≤20MB (pdf/office/gambar/zip/
txt/csv), lingkup valid, unit wajib sesuai lingkup + exists.

## 12. Testing

`DocumentTest` — 9 test: unggah+simpan file, **matriks visibilitas department**
(anggota ✓, kepala division ✓, department lain ✗ termasuk di daftar), division,
paljaya (baca semua, publikasi butuh izin), tolak unggah ke unit orang lain,
notifikasi audiens (bukan pengunggah), hapus (file ikut terhapus; bukan
pengunggah 403), tolak file besar/berbahaya, guest redirect.

## 13. Security Consideration

File di disk privat — tidak pernah ada URL publik; unduhan streaming lewat
policy; whitelist ekstensi + batas 20MB; nama file asli hanya untuk unduhan
(path disimpan ter-hash oleh Laravel); semua mutasi ter-audit otomatis.

## 14. Integration Consideration

Modul lain melampirkan dokumen dengan membuat record ber-lingkup via
`DocumentService`; Workflow kelak merujuk dokumen dalam approval; AI (Document
Analysis, ADR-005) membaca lewat service ini sehingga otomatis menghormati
lingkup pembaca.

## 15. Deployment Consideration

Pastikan `storage/` writable dan masuk backup (file dokumen berada di sana,
bukan di database). `php artisan storage:link` TIDAK diperlukan (disk privat).

## 16. Status Dokumentasi

Selesai. Menyusul: versi dokumen, folder/taksonomi kategori terkontrol,
pencarian isi dokumen, attachment polimorfik ke entity lain, retensi.
