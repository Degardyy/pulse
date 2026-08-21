# Membuat Modul PULSE

Panduan konvensi modul (ADR-002). Semua modul — Core, Budget, division portal — mengikuti
struktur dan aturan yang sama.

## Struktur Direktori

```
modules/<Name>/
├── Providers/<Name>ServiceProvider.php   # wajib — entry point modul
├── Http/
│   ├── Controllers/                      # tipis: validasi → service → response
│   ├── Middleware/
│   └── Requests/                         # Form Request (validasi)
├── Models/
├── Services/                             # business logic — kontrak untuk HTTP, API, dan AI
├── Policies/                             # authorization per model
├── routes/
│   └── web.php                           # (opsional api.php)
├── Database/                             # PascalCase agar PSR-4 valid (Modules\<Name>\Database\Seeders)
│   ├── Migrations/
│   └── Seeders/
├── resources/
│   ├── views/                            # namespace view: <name>::
│   │   └── components/                   # anonymous components: <x-<name>::...>
│   └── lang/                             # namespace lang: <name>::
└── tests/                                # Feature & Unit tests modul
```

## Registrasi

1. Namespace `Modules\<Name>\` sudah ter-autoload dari `modules/` (composer.json).
2. Daftarkan provider di `config/modules.php`:

```php
'modules' => [
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\<Name>\Providers\<Name>ServiceProvider::class,
],
```

3. Provider modul meng-extend `App\Modules\ModuleServiceProvider` dan cukup menyetel
   nama modul — routes, views, lang, dan migrations dimuat otomatis dari konvensi di atas:

```php
namespace Modules\Helpdesk\Providers;

use App\Modules\ModuleServiceProvider;

class HelpdeskServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Helpdesk';
}
```

## Aturan Boundary (wajib)

1. Modul boleh depend pada **Core**; Core tidak boleh depend pada modul lain.
2. Antar modul non-Core: hanya lewat **interface (service contract)** atau **event** —
   tidak boleh query tabel modul lain atau `new` service modul lain secara langsung.
3. **Table prefix** per modul: `core_`, `budget_`, `it_`, `proc_`, dst.
4. Business logic di **Services/**, bukan di controller — service adalah satu-satunya
   jalur yang kelak diekspos ke REST API dan AI gateway (ADR-005).
5. Setiap model yang dimutasi user wajib punya **Policy** dan tercatat di **audit trail**
   (setelah Core tersedia).
6. Route diberi prefix + name modul (`/it/helpdesk`, name `it.helpdesk.*`) dan middleware
   auth + permission.

## Definition of Done (16 butir)

Setiap modul/iterasi didokumentasikan di `docs/modules/<name>.md` yang mencakup:

1. Objective · 2. Business requirement · 3. Architecture · 4. ERD/logical model ·
5. Migration plan · 6. Model · 7. Controller/Service · 8. Policy/Authorization ·
9. Route · 10. UI/UX · 11. Validation · 12. Testing · 13. Security consideration ·
14. Integration consideration · 15. Deployment consideration · 16. Documentation.
