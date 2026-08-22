<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Modules\Core\Models\PositionAssignment;
use Modules\Core\Services\Dashboard\WidgetRegistry;
use Modules\Core\Services\EmployeeService;
use Modules\Core\Services\NavigationService;
use Modules\Core\Services\OrganizationService;
use Modules\Core\Services\Workflow\WorkflowService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly OrganizationService $organization,
        private readonly EmployeeService $employees,
        private readonly NavigationService $navigation,
        private readonly WorkflowService $workflow,
    ) {}

    public function __invoke(): View
    {
        $user = auth()->user();
        $hour = (int) now()->format('G');

        $department = $user->employee?->activeAssignments()
            ->with('position.department.division')
            ->first()?->position?->department;

        return view('core::home', [
            'greeting' => match (true) {
                $hour < 11 => 'Selamat pagi',
                $hour < 15 => 'Selamat siang',
                $hour < 19 => 'Selamat sore',
                default => 'Selamat malam',
            },
            'firstName' => explode(' ', trim($user->name))[0],
            'today' => now()->locale('id')->translatedFormat('l, j F Y'),
            'department' => $department,
            'departmentHead' => $department?->positions()
                ->with('currentAssignment.employee')
                ->first()?->currentAssignment,
            'departmentOfficials' => $department
                ? PositionAssignment::whereNull('ended_at')
                    ->whereHas('position', fn ($q) => $q->where('department_id', $department->id))
                    ->count()
                : null,
            'orgCounts' => $this->organization->counts(),
            'employeeCounts' => $this->employees->counts(),

            // Attention: the approvals item is LIVE (workflow engine); the
            // rest remain demonstration fixtures until their modules land
            // (Helpdesk tasks, Budget alerts).
            'attention' => array_values(array_filter([
                ($approvals = $this->workflow->pendingFor($user)->count()) > 0
                    ? ['icon' => 'check-circle', 'tone' => 'accent', 'text' => "{$approvals} persetujuan menunggu Anda", 'url' => route('core.approvals.index')]
                    : null,
                ['icon' => 'clock', 'tone' => 'warning', 'text' => '5 tugas jatuh tempo hari ini', 'url' => null],
                ['icon' => 'alert', 'tone' => 'danger', 'text' => '1 peringatan anggaran perlu ditinjau', 'url' => null],
            ])),
            'tasks' => [
                ['label' => 'Tinjau dokumen pengadaan server', 'due' => 'Hari ini', 'done' => false],
                ['label' => 'Setujui permintaan akses aplikasi', 'due' => 'Hari ini', 'done' => false],
                ['label' => 'Tindak lanjut tiket #IT-0241', 'due' => 'Besok', 'done' => false],
                ['label' => 'Finalisasi laporan bulanan', 'due' => 'Selesai', 'done' => true],
            ],
            'recents' => [
                ['icon' => 'document', 'label' => 'Struktur Organisasi 1 Juli 2026', 'meta' => 'Dokumen'],
                ['icon' => 'users', 'label' => 'Daftar pejabat struktural', 'meta' => 'Pegawai'],
                ['icon' => 'building', 'label' => 'Information Technology & Procurement', 'meta' => 'Organisasi'],
            ],
            'aiInsight' => 'Realisasi anggaran department Anda berada di atas tren rata-rata bulanan. Tinjau komitmen infrastruktur sebelum akhir triwulan.',
            'widgets' => app(WidgetRegistry::class)->forUser($user),
        ]);
    }
}
