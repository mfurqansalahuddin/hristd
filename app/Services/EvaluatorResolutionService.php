<?php

namespace App\Services;

use App\Models\Department;
use App\Models\KpiEvaluatorWeight;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menghitung siapa Penilai 1-3 seorang pegawai (job_level 2-4), on-the-fly
 * dari department_id + job_level (plan.md §4-§5). Tidak ada FK statis —
 * dipanggil ulang tiap dibutuhkan (form approval, form penilaian, dsb).
 */
class EvaluatorResolutionService
{
    /**
     * @return list<array{slot: string, evaluator: ?User, weight: int, is_peer: bool}>
     */
    public function resolveFor(User $user, int $periodId): array
    {
        $department = $user->department;

        if (! $department) {
            return [];
        }

        return match ((int) $user->job_level) {
            4 => $this->resolveStaf($user, $department, $periodId),
            3 => $this->resolveKasi($user, $department, $periodId),
            2 => $this->resolveKabag($user, $department, $periodId),
            default => [], // Direksi (job_level 1) di luar cakupan KPI bulanan, §5.2
        };
    }

    /**
     * Kebalikan dari resolveFor(): semua orang yang harus dinilai $evaluator pada
     * periode ini, plus slot/bobotnya. Dihitung dengan reuse resolveFor() per
     * calon (bukan aturan terpisah yang bisa divergen) — org kecil (~ratusan
     * pegawai), aman secara performa untuk skala Perumdam.
     *
     * @return Collection<int, array{evaluee: User, slot: string, weight: int, is_peer: bool}>
     */
    public function evaluateesFor(User $evaluator, int $periodId): Collection
    {
        return User::whereIn('job_level', [2, 3, 4])
            ->where('id', '!=', $evaluator->id)
            ->get()
            ->map(function (User $evaluee) use ($evaluator, $periodId) {
                $slot = collect($this->resolveFor($evaluee, $periodId))
                    ->first(fn (array $s) => $s['evaluator']?->id === $evaluator->id);

                return $slot ? [
                    'evaluee' => $evaluee,
                    'slot' => $slot['slot'],
                    'weight' => $slot['weight'],
                    'is_peer' => $slot['is_peer'],
                ] : null;
            })
            ->filter()
            ->values();
    }

    /** Staf (job_level 4) — posisi di Seksi, atau langsung di Unit TI (kasus solo). */
    private function resolveStaf(User $staf, Department $seksi, int $periodId): array
    {
        $bagian = $seksi->type === 'SEKSI' ? $seksi->parent : $seksi;
        $kasi = $seksi->type === 'SEKSI' ? $this->headOf($seksi, 3) : null;
        $kabag = $this->headOf($bagian, 2);

        $penilai1 = $kasi ?? $kabag;
        $penilai2 = $kabag;

        $peers = $this->peersInDepartment($seksi->id, 4, $staf->id);
        $penilai3 = $this->cyclicEvaluator($peers, $staf, $periodId) ?? $kasi ?? $kabag;

        return $this->withWeights(4, $penilai1, $penilai2, $penilai3);
    }

    /** Kasi (job_level 3) — posisi di Seksi, dinilai 3 pihak (revert 2026-07-15, §5.2). */
    private function resolveKasi(User $kasi, Department $seksi, int $periodId): array
    {
        $bagian = $seksi->parent;
        $kabag = $bagian ? $this->headOf($bagian, 2) : null;
        $direkturBidang = $bagian ? $this->direkturBidangOf($bagian) : null;

        $peerSeksiIds = $bagian
            ? Department::where('parent_department_id', $bagian->id)->where('type', 'SEKSI')->pluck('id')
            : collect();
        $peers = $peerSeksiIds->isEmpty()
            ? collect()
            : $this->peersInDepartments($peerSeksiIds, 3, $kasi->id);

        $penilai3 = $this->cyclicEvaluator($peers, $kasi, $periodId) ?? $kabag;

        return $this->withWeights(3, $kabag, $direkturBidang, $penilai3);
    }

    /** Kabag/Kacab/Kanit/Staf Ahli (job_level 2) — selalu punya atasan lengkap. */
    private function resolveKabag(User $kabag, Department $bagian, int $periodId): array
    {
        $direkturBidang = $this->direkturBidangOf($bagian);
        $direkturUtama = $this->direkturUtama();

        // Staf Ahli tidak punya rekan setingkat langsung — rekan diacak dari
        // seluruh job_level 2, bukan dibatasi 1 direktorat (§5.2 pengecualian).
        $peerPool = $bagian->type === 'STAF_AHLI'
            ? User::where('job_level', 2)->where('id', '!=', $kabag->id)->get()
            : $this->peersInDirectorate($bagian, $kabag->id);

        $penilai3 = $this->cyclicEvaluator($peerPool, $kabag, $periodId) ?? $direkturBidang;

        return $this->withWeights(2, $direkturBidang, $direkturUtama, $penilai3);
    }

    /** Kepala department tertentu (job_level 2 atau 3) — asumsi 0-1 orang per node. */
    private function headOf(Department $department, int $jobLevel): ?User
    {
        return User::where('department_id', $department->id)->where('job_level', $jobLevel)->first();
    }

    /** Direktur Utama — satu-satunya node DIREKSI tanpa parent. */
    public function direkturUtama(): ?User
    {
        $root = Department::where('type', 'DIREKSI')->whereNull('parent_department_id')->first();

        return $root ? $this->headOf($root, 1) : null;
    }

    /**
     * Direktur Bidang — jalan naik dari department manapun sampai ketemu node
     * DIREKSI non-root (Direktur ADM & Keuangan / Direktur Teknik, §3.2). Tidak
     * bergantung kolom `directorate` di tiap Bagian (belum tentu terisi) —
     * cukup posisi di pohon organisasi.
     */
    public function direkturBidangOf(Department $department): ?User
    {
        $node = $department;

        while ($node && ! ($node->type === 'DIREKSI' && $node->parent_department_id !== null)) {
            $node = $node->parent;
        }

        return $node ? $this->headOf($node, 1) : null;
    }

    /** Staf/Kasi lain (excl. diri sendiri) di 1 department yang sama. */
    private function peersInDepartment(int $departmentId, int $jobLevel, int $excludeUserId): Collection
    {
        return User::where('department_id', $departmentId)
            ->where('job_level', $jobLevel)
            ->where('id', '!=', $excludeUserId)
            ->get();
    }

    /** Sama seperti peersInDepartment(), tapi lintas beberapa department (Kasi selingkup 1 Bagian). */
    private function peersInDepartments(Collection $departmentIds, int $jobLevel, int $excludeUserId): Collection
    {
        return User::whereIn('department_id', $departmentIds)
            ->where('job_level', $jobLevel)
            ->where('id', '!=', $excludeUserId)
            ->get();
    }

    /** Kabag-setara lain (job_level 2) dalam direktorat yang sama (§5.2), excl. Staf Ahli & diri sendiri. */
    private function peersInDirectorate(Department $bagian, int $excludeUserId): Collection
    {
        $direktur = $this->direkturBidangOf($bagian);

        if (! $direktur) {
            return collect();
        }

        $siblingDeptIds = Department::where('parent_department_id', $direktur->department_id)
            ->where('type', '!=', 'STAF_AHLI')
            ->pluck('id');

        return $this->peersInDepartments($siblingDeptIds, 2, $excludeUserId);
    }

    /**
     * Siapa yang menilai $self dalam siklus rekan sejawat (§5.3): urutan diacak
     * secara deterministik per periode (md5 dari period+id, bukan RNG global —
     * supaya hasilnya stabil dipanggil berkali-kali dalam periode yang sama,
     * tapi beda antar periode), lalu orang SEBELUM $self di siklus itu yang
     * menilai $self. Untuk grup 2 orang ini otomatis jadi saling menilai.
     */
    private function cyclicEvaluator(Collection $peers, User $self, int $periodId): ?User
    {
        if ($peers->isEmpty()) {
            return null;
        }

        $group = $peers->push($self);

        $ordered = $group->sortBy(fn (User $u) => md5($periodId.'-'.$u->id))->values();
        $index = $ordered->search(fn (User $u) => $u->id === $self->id);
        $prevIndex = ($index - 1 + $ordered->count()) % $ordered->count();

        return $ordered[$prevIndex];
    }

    /** @return list<array{slot: string, evaluator: ?User, weight: int, is_peer: bool}> */
    private function withWeights(int $jobLevel, ?User $penilai1, ?User $penilai2, ?User $penilai3): array
    {
        $weights = KpiEvaluatorWeight::where('job_level', $jobLevel)->pluck('weight', 'slot');

        return [
            ['slot' => 'PENILAI_1', 'evaluator' => $penilai1, 'weight' => $weights['PENILAI_1'] ?? 0, 'is_peer' => false],
            ['slot' => 'PENILAI_2', 'evaluator' => $penilai2, 'weight' => $weights['PENILAI_2'] ?? 0, 'is_peer' => false],
            ['slot' => 'PENILAI_3', 'evaluator' => $penilai3, 'weight' => $weights['PENILAI_3'] ?? 0, 'is_peer' => true],
        ];
    }
}
