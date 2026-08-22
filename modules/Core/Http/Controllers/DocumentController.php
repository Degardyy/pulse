<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Http\Requests\StoreDocumentRequest;
use Modules\Core\Models\Department;
use Modules\Core\Models\Division;
use Modules\Core\Models\Document;
use Modules\Core\Services\DocumentService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController
{
    use AuthorizesRequests;

    public function __construct(private readonly DocumentService $documents) {}

    public function index(Request $request): View
    {
        $filter = $request->query('lingkup');

        $documents = Document::query()
            ->visibleTo($request->user())
            ->when($filter === 'paljaya', fn ($q) => $q->where('visibility', Document::VISIBILITY_PALJAYA))
            ->when($filter === 'unit', fn ($q) => $q->whereIn('visibility', [
                Document::VISIBILITY_DIVISION, Document::VISIBILITY_DEPARTMENT,
            ]))
            ->with(['uploader', 'division', 'department'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('core::documents.index', [
            'documents' => $documents,
            'filter' => $filter,
            'canCreate' => $request->user()->can('create', Document::class),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Document::class);

        $user = $request->user();
        $units = $user->organizationUnitIds();
        $manage = $user->hasPermission('core.documents.manage');

        return view('core::documents.create', [
            'canPublishOrg' => $manage || $user->hasPermission('core.documents.publish-org'),
            'divisions' => ($manage ? Division::query() : Division::whereIn('id', $units['divisions']))
                ->where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'departments' => ($manage ? Department::query() : Department::whereIn('id', $units['departments']))
                ->where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = $this->documents->store(
            $request->user(),
            $request->safe()->except('file'),
            $request->file('file'),
        );

        return redirect()->route('core.documents.index')
            ->with('status', "Dokumen \"{$document->title}\" dibagikan ke {$document->visibilityLabel()}.");
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $this->documents->delete($document);

        return redirect()->route('core.documents.index')
            ->with('status', "Dokumen \"{$document->title}\" dihapus.");
    }
}
