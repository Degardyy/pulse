<?php

namespace Modules\Core\Providers;

/**
 * Core's own registrations into the dashboard & reporting foundations.
 * Kept out of CoreServiceProvider::boot for readability; called from there.
 */

use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Document;
use Modules\Core\Models\Employee;
use Modules\Core\Models\User;
use Modules\Core\Services\Dashboard\WidgetRegistry;
use Modules\Core\Services\Reporting\ReportRegistry;

class CoreDashboardProvider
{
    public static function register(WidgetRegistry $widgets, ReportRegistry $reports): void
    {
        $widgets->register(
            key: 'core.recent-documents',
            title: 'Dokumen Terbaru',
            view: 'core::widgets.recent-documents',
            sort: 30,
            data: fn (User $user) => [
                'documents' => Document::query()
                    ->visibleTo($user)
                    ->where('status', Document::STATUS_PUBLISHED)
                    ->latest()
                    ->limit(5)
                    ->get(),
            ],
        );

        $reports->register(
            key: 'core.officials',
            title: 'Pejabat Struktural',
            description: 'Daftar pejabat, jabatan, unit, dan status Plt sesuai data organisasi terkini.',
            headers: ['Nama', 'Jabatan', 'Level', 'Status'],
            rows: function () {
                foreach (Employee::with('activeAssignments.position')->orderBy('name')->get() as $employee) {
                    foreach ($employee->activeAssignments as $assignment) {
                        yield [
                            $employee->name,
                            $assignment->position->name,
                            $assignment->position->level,
                            $assignment->is_acting ? 'Plt' : 'Definitif',
                        ];
                    }
                }
            },
        );

        $reports->register(
            key: 'core.audit-trail',
            title: 'Audit Trail',
            description: 'Seluruh jejak perubahan data dan aktivitas autentikasi (CSV).',
            headers: ['Waktu', 'Pengguna', 'Event', 'Objek', 'Sebelum', 'Sesudah', 'IP'],
            permission: 'core.audit.view',
            rows: function () {
                foreach (AuditLog::with('user')->latest('id')->lazy() as $log) {
                    yield [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user?->name ?? 'System',
                        $log->event,
                        $log->subjectLabel(),
                        $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '',
                        $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '',
                        $log->ip_address,
                    ];
                }
            },
        );
    }
}
