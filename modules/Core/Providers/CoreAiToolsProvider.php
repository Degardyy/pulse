<?php

namespace Modules\Core\Providers;

use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Document;
use Modules\Core\Models\User;
use Modules\Core\Services\Ai\AiToolRegistry;
use Modules\Core\Services\EmployeeService;
use Modules\Core\Services\OrganizationService;
use Modules\Core\Services\Workflow\WorkflowService;

/**
 * Core's AI-callable tools (ADR-005). Each tool wraps an existing authorized
 * service path — the AI can never see more than the requesting user can.
 */
class CoreAiToolsProvider
{
    public static function register(AiToolRegistry $tools): void
    {
        $tools->register(
            name: 'organization.summary',
            description: 'Ringkasan struktur organisasi Paljaya: jumlah direktorat, division, department, pejabat, posisi vacant.',
            parameters: [],
            handler: fn (User $user) => [
                'organisasi' => app(OrganizationService::class)->counts(),
                'kepegawaian' => app(EmployeeService::class)->counts(),
            ],
        );

        $tools->register(
            name: 'documents.search',
            description: 'Cari dokumen yang boleh dibaca pengguna berdasarkan kata kunci judul/kategori.',
            parameters: ['query' => 'Kata kunci pencarian'],
            handler: fn (User $user, array $args) => Document::query()
                ->visibleTo($user)
                ->where('status', Document::STATUS_PUBLISHED)
                ->when($args['query'] ?? null, fn ($q, $query) => $q->where(
                    fn ($q) => $q->where('title', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%"),
                ))
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (Document $d) => [
                    'id' => $d->id,
                    'judul' => $d->title,
                    'lingkup' => $d->visibilityLabel(),
                    'kategori' => $d->category,
                ])
                ->all(),
        );

        $tools->register(
            name: 'approvals.pending',
            description: 'Daftar permintaan persetujuan yang menunggu keputusan pengguna.',
            parameters: [],
            handler: fn (User $user) => app(WorkflowService::class)->pendingFor($user)
                ->map(fn ($instance) => [
                    'id' => $instance->id,
                    'jenis' => $instance->definition->name,
                    'subjek' => $instance->subjectLabel(),
                    'pemohon' => $instance->requester->name,
                    'diajukan' => $instance->created_at->toDateTimeString(),
                ])
                ->all(),
        );

        $tools->register(
            name: 'audit.recent',
            description: 'Jejak audit terbaru (khusus pemegang izin audit).',
            parameters: [],
            permission: 'core.audit.view',
            handler: fn (User $user) => AuditLog::with('user')->latest('id')->limit(10)->get()
                ->map(fn (AuditLog $log) => [
                    'waktu' => $log->created_at->toDateTimeString(),
                    'pengguna' => $log->user?->name ?? 'System',
                    'event' => $log->event,
                    'objek' => $log->subjectLabel(),
                ])
                ->all(),
        );
    }
}
