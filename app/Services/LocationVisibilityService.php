<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menghitung siapa yang boleh dilihat siapa di Live Location (mobile-app.md
 * §4) — beda scope dari EvaluatorResolutionService: siapa boleh LIHAT siapa,
 * bukan siapa MENILAI siapa.
 */
class LocationVisibilityService
{
    /**
     * @return Collection<int, int> daftar user_id yang boleh dilihat $user
     */
    public function visibleUserIdsFor(User $user, string $scope = 'peers', ?string $levelFilter = null, ?int $departmentFilter = null): Collection
    {
        if ((int) $user->job_level === 4) {
            return $this->staffPeers($user);
        }

        if ((int) $user->job_level === 1) {
            return $this->direksiScope($levelFilter, $departmentFilter);
        }

        // Kasi (3) & Kabag-setara (2).
        return $scope === 'subordinates'
            ? $this->subordinatesOf($user)
            : $this->pejabatCompanyWide($user->id);
    }

    /** Staf: hanya rekan 1 seksi/departemen yang sama (§4). */
    private function staffPeers(User $staf): Collection
    {
        return User::where('department_id', $staf->department_id)
            ->where('job_level', 4)
            ->where('id', '!=', $staf->id)
            ->pluck('id');
    }

    /** Peers default utk pejabat (job_level 2-3): seluruh pejabat se-Perumdam, lintas bagian & level. */
    private function pejabatCompanyWide(int $excludeUserId): Collection
    {
        return User::whereIn('job_level', [1, 2, 3])
            ->where('id', '!=', $excludeUserId)
            ->pluck('id');
    }

    /**
     * Switch "Lihat Bawahan" (§4, diperluas 2026-07-16): staf di 1 Bagian induk
     * penuh — utk Kasi ini naik dari "seksinya sendiri" ke seluruh Bagian
     * (semua Seksi sebagian); utk Kabag sudah otomatis sama karena dia
     * memimpin 1 Bagian penuh.
     */
    private function subordinatesOf(User $pejabat): Collection
    {
        $bagian = (int) $pejabat->job_level === 3
            ? $pejabat->department?->parent
            : $pejabat->department;

        if (! $bagian) {
            return collect();
        }

        return User::whereIn('department_id', $this->staffDepartmentIdsUnder($bagian))
            ->where('job_level', 4)
            ->pluck('id');
    }

    /** Semua department id tempat staf (job_level 4) melekat di bawah 1 node Bagian-setara. */
    private function staffDepartmentIdsUnder(Department $bagian): array
    {
        $seksiIds = Department::where('parent_department_id', $bagian->id)->where('type', 'SEKSI')->pluck('id')->all();

        // Kasus solo (§3.3): UNIT tanpa Seksi anak, staf melekat langsung ke node Unit itu sendiri.
        return $bagian->type === 'UNIT' ? [...$seksiIds, $bagian->id] : $seksiIds;
    }

    /**
     * Filter khusus Direksi (§4.1 mobile-app.md, direvisi 2026-07-16): Direksi
     * tidak absen/tidak masuk cakupan KPI, jadi cukup 2 filter — tidak ada
     * mode "peers" default seperti level lain.
     */
    private function direksiScope(?string $levelFilter, ?int $departmentFilter): Collection
    {
        if ($levelFilter === 'everyone') {
            $query = User::whereIn('job_level', [1, 2, 3, 4]);

            if ($departmentFilter && $bagian = Department::find($departmentFilter)) {
                $query->whereIn('department_id', [$bagian->id, ...$this->staffDepartmentIdsUnder($bagian)]);
            }

            return $query->pluck('id');
        }

        // Default: "Pejabat" — Kabag-setara & Kasi saja, exclude sesama Direksi.
        return User::whereIn('job_level', [2, 3])->pluck('id');
    }
}
