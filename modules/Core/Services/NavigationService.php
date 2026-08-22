<?php

namespace Modules\Core\Services;

use Modules\Core\Models\User;

/**
 * Single source of truth for shell navigation: the sidebar, the mobile bottom
 * bar, and the command palette all render from this list, so a module that
 * registers here appears everywhere at once. Permission-gated items are
 * filtered per user.
 */
class NavigationService
{
    /**
     * @return list<array{label: string, items: list<array{label: string, icon: string, route: string, active: string, keywords: string}>}>
     */
    public function sections(User $user): array
    {
        $sections = [
            [
                'label' => 'Workspace',
                'items' => [
                    ['label' => 'Beranda', 'icon' => 'home', 'route' => 'core.dashboard', 'active' => 'core.dashboard', 'keywords' => 'home beranda workspace'],
                    ['label' => 'Dokumen', 'icon' => 'document', 'route' => 'core.documents.index', 'active' => 'core.documents.*', 'keywords' => 'dokumen file berkas unggah dokumen sk surat'],
                ],
            ],
            [
                'label' => 'Organisasi',
                'items' => [
                    ['label' => 'Struktur Organisasi', 'icon' => 'building', 'route' => 'core.organization.index', 'active' => 'core.organization.*', 'keywords' => 'organisasi struktur direktorat division department'],
                    ['label' => 'Pegawai', 'icon' => 'users', 'route' => 'core.employees.index', 'active' => 'core.employees.*', 'keywords' => 'pegawai pejabat karyawan employee'],
                ],
            ],
        ];

        $admin = [];

        if ($user->hasPermission('core.users.view')) {
            $admin[] = ['label' => 'Pengguna', 'icon' => 'shield', 'route' => 'core.admin.users.index', 'active' => 'core.admin.users.*', 'keywords' => 'pengguna akun user admin role akses'];
        }

        if ($user->hasPermission('core.audit.view')) {
            $admin[] = ['label' => 'Audit Trail', 'icon' => 'clock', 'route' => 'core.admin.audit.index', 'active' => 'core.admin.audit.*', 'keywords' => 'audit trail log jejak riwayat perubahan'];
        }

        if ($admin !== []) {
            $sections[] = ['label' => 'Administrasi', 'items' => $admin];
        }

        return $sections;
    }

    /**
     * The user's current workspace context for the shell: their department or
     * division (via the employee link) or a personal workspace fallback.
     * Division portals will make this switchable (roadmap Stage 3).
     *
     * @return array{org: string, context: string}
     */
    public function workspace(User $user): array
    {
        $position = $user->employee?->activeAssignments()
            ->with('position.department', 'position.division', 'position.directorate')
            ->first()?->position;

        $context = $position?->department?->name
            ?? $position?->division?->name
            ?? $position?->directorate?->name
            ?? 'Personal Workspace';

        return ['org' => 'Perumda Paljaya', 'context' => $context];
    }

    /**
     * Flat list for the mobile bottom bar (first items only).
     *
     * @return list<array{label: string, icon: string, route: string, active: string}>
     */
    public function primary(User $user, int $limit = 4): array
    {
        $items = [];

        foreach ($this->sections($user) as $section) {
            foreach ($section['items'] as $item) {
                $items[] = $item;
            }
        }

        return array_slice($items, 0, $limit);
    }
}
