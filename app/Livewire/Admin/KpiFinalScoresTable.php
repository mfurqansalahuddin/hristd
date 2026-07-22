<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman "Nilai Akhir & Persentase Gaji" — mengisi item Fase B (kpi_final_scores
 * belum punya controller/route/view). 2 mode: per periode (rekap untuk Keuangan,
 * export CSV) dan per pegawai (riwayat lintas periode, tanpa breakdown per-evaluator
 * — cuma 5 skor kategori + grand total + persentase, menyiapkan data yang sama
 * dibutuhkan layar profil mobile nanti).
 */
class KpiFinalScoresTable extends Component
{
    use PaginatesRows, WithPagination;

    #[Url(history: true)]
    public string $mode = 'periode';

    #[Url(history: true)]
    public ?int $periodId = null;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $selectedUserId = null;

    public function mount(): void
    {
        $this->periodId ??= KpiPeriod::orderByDesc('year')->orderByDesc('month')->value('id');
    }

    public function updatedMode(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->selectedUserId = null;
        $this->resetPage();
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.kpi-final-scores-table', [
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'periods' => KpiPeriod::orderByDesc('year')->orderByDesc('month')->get(),
            'scores' => $this->mode === 'pegawai' ? $this->perPegawaiScores() : $this->perPeriodeScores(),
            'matchingUsers' => $this->mode === 'pegawai' && $this->search !== '' ? $this->matchingUsers() : collect(),
            'selectedUser' => $this->selectedUserId ? User::find($this->selectedUserId) : null,
        ]);
    }

    private function perPeriodeScores()
    {
        return KpiFinalScore::with(['user.department'])
            ->when($this->periodId, fn ($query) => $query->where('period_id', $this->periodId))
            ->orderByDesc('grand_total_score')
            ->paginate($this->perPage());
    }

    private function perPegawaiScores()
    {
        if (! $this->selectedUserId) {
            return null;
        }

        return KpiFinalScore::with('period')
            ->where('user_id', $this->selectedUserId)
            ->join('kpi_periods', 'kpi_periods.id', '=', 'kpi_final_scores.period_id')
            ->orderByDesc('kpi_periods.year')
            ->orderByDesc('kpi_periods.month')
            ->select('kpi_final_scores.*')
            ->paginate($this->perPage());
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function matchingUsers()
    {
        return User::whereIn('job_level', [2, 3, 4])
            ->where(fn ($query) => $query->where('name', 'like', "%{$this->search}%")->orWhere('nik', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();
    }
}
