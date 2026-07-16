<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\Attendance;
use App\Models\Location;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AttendancesTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $date = '';

    #[Url(history: true)]
    public string $location = '';

    #[Url(history: true)]
    public string $statusMasuk = '';

    #[Url(history: true)]
    public string $statusPulang = '';

    public function mount(): void
    {
        if ($this->date === '') {
            $this->date = now()->toDateString();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function updatedLocation(): void
    {
        $this->resetPage();
    }

    public function updatedStatusMasuk(): void
    {
        $this->resetPage();
    }

    public function updatedStatusPulang(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'location', 'statusMasuk', 'statusPulang']);
        $this->date = now()->toDateString();
        $this->resetPage();

        // the date-picker lives under wire:ignore (flatpickr owns its DOM), so tell it
        // to update its displayed value directly instead of relying on a Livewire re-render.
        $this->dispatch('datepicker-set-date', id: 'attendance-date', date: $this->date);
    }

    /**
     * Approval kehadiran (telat/pulang cepat/luar geofence) sekarang wewenang
     * HR sepenuhnya lewat tombol ini — bukan atasan (plan.md §8.1, 2026-07-16).
     */
    public function approve(int $attendanceId): void
    {
        Attendance::whereKey($attendanceId)->update(['supervisor_approval' => 'APPROVED']);

        session()->flash('success', 'Kehadiran disetujui.');
    }

    public function reject(int $attendanceId): void
    {
        Attendance::whereKey($attendanceId)->update(['supervisor_approval' => 'REJECTED']);

        session()->flash('success', 'Kehadiran ditolak.');
    }

    protected function sortableColumns(): array
    {
        return ['name', 'date', 'clock_in', 'clock_out'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }

    public function render()
    {
        $locations = Location::all();
        $sort = $this->sort;
        $perPage = $this->perPage();

        $attendances = Attendance::with('user')
            ->whereDate('date', $this->date ?: now()->toDateString())
            ->get()
            ->filter(fn (Attendance $attendance) => $this->matchesSearch($attendance))
            ->filter(fn (Attendance $attendance) => $this->matchesLocationFilter($attendance, $locations))
            ->filter(fn (Attendance $attendance) => $this->matchesStatusMasukFilter($attendance))
            ->filter(fn (Attendance $attendance) => $this->matchesStatusPulangFilter($attendance))
            ->sortBy(
                fn (Attendance $attendance) => $sort === 'name' ? $attendance->user?->name : $attendance->{$sort},
                SORT_REGULAR,
                $this->direction === 'desc'
            )
            ->values();

        $page = $this->getPage();
        $paginated = new LengthAwarePaginator(
            $attendances->slice(($page - 1) * $perPage, $perPage)->values(),
            $attendances->count(),
            $perPage,
            $page,
        );

        return view('livewire.admin.attendances-table', [
            'attendances' => $paginated,
            'locations' => $locations,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }

    private function matchesSearch(Attendance $attendance): bool
    {
        if ($this->search === '') {
            return true;
        }

        $needle = mb_strtolower($this->search);

        return str_contains(mb_strtolower($attendance->user?->name ?? ''), $needle)
            || str_contains(mb_strtolower($attendance->user?->nik ?? ''), $needle);
    }

    private function matchesLocationFilter(Attendance $attendance, Collection $locations): bool
    {
        if ($this->location === '') {
            return true;
        }

        if ($this->location === 'OUTSIDE') {
            return ($attendance->clock_in && ! $attendance->matchedLocation('clock_in', $locations))
                || ($attendance->clock_out && ! $attendance->matchedLocation('clock_out', $locations));
        }

        $matchesSelectedLocation = fn (?Location $matched) => $matched && (string) $matched->id === $this->location;

        return $matchesSelectedLocation($attendance->matchedLocation('clock_in', $locations))
            || $matchesSelectedLocation($attendance->matchedLocation('clock_out', $locations));
    }

    private function matchesStatusMasukFilter(Attendance $attendance): bool
    {
        return match ($this->statusMasuk) {
            'TEPAT_WAKTU' => $attendance->statusMasuk() === 'TEPAT_WAKTU',
            'TERLAMBAT' => $attendance->statusMasuk() === 'TERLAMBAT',
            default => true,
        };
    }

    private function matchesStatusPulangFilter(Attendance $attendance): bool
    {
        return match ($this->statusPulang) {
            'TEPAT_WAKTU' => $attendance->statusPulang() === 'TEPAT_WAKTU',
            'CEPAT' => $attendance->statusPulang() === 'CEPAT',
            default => true,
        };
    }
}
