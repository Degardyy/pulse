<?php

namespace Modules\Core\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Core\Models\AuditLog;

class AuditLogController
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('core.audit.view');

        $event = $request->query('event');

        $logs = AuditLog::query()
            ->with(['user', 'auditable'])
            ->when($event, fn ($q) => $q->where('event', $event))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('core::admin.audit.index', [
            'logs' => $logs,
            'event' => $event,
            'events' => [
                AuditLog::EVENT_CREATED => 'Dibuat',
                AuditLog::EVENT_UPDATED => 'Diubah',
                AuditLog::EVENT_DELETED => 'Dihapus',
                AuditLog::EVENT_ROLES_SYNCED => 'Role diubah',
                AuditLog::EVENT_LOGIN => 'Login',
                AuditLog::EVENT_LOGOUT => 'Logout',
            ],
        ]);
    }
}
