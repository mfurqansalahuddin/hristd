<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\Department;
use App\Models\KpiMandatoryEvent;
use App\Models\KpiMandatoryEventPreset;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MandatoryEventsTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    public bool $showCreateForm = false;

    public string $newName = '';

    public string $newDate = '';

    public string $newTime = '';

    public string $newGroupingMode = 'per_bagian';

    /** @var array<int, int> */
    public array $selectedUserIds = [];

    /** Departemen yang sudah di-bulk-add, dilacak supaya savePreset() tahu bagian mana yang dari departemen vs manual. */
    public array $bulkDepartmentIds = [];

    /** Dibump tiap selectedUserIds dimutasi dari server, supaya x-form.person-select (Alpine) remount dan baca ulang nilainya. */
    public int $participantsVersion = 0;

    public string $presetName = '';

    public function openCreateForm(): void
    {
        $this->reset(['newName', 'newDate', 'newTime', 'newGroupingMode', 'selectedUserIds', 'bulkDepartmentIds', 'presetName']);
        $this->participantsVersion++;
        $this->showCreateForm = true;
        $this->clearDateTimePickers();
    }

    public function cancelCreateForm(): void
    {
        $this->showCreateForm = false;
    }

    public function setGroupingMode(string $mode): void
    {
        if (in_array($mode, ['per_bagian', 'gabung'], true)) {
            $this->newGroupingMode = $mode;
        }
    }

    public function addDepartmentMembers(string $departmentId): void
    {
        if ($departmentId === '') {
            return;
        }

        $departmentId = (int) $departmentId;

        if (! in_array($departmentId, $this->bulkDepartmentIds, true)) {
            $this->bulkDepartmentIds[] = $departmentId;
        }

        $this->selectedUserIds = collect($this->selectedUserIds)
            ->merge($this->departmentMemberIds([$departmentId]))
            ->unique()->values()->all();

        $this->participantsVersion++;
    }

    public function selectAllUsers(): void
    {
        $this->selectedUserIds = User::pluck('id')->all();
        $this->participantsVersion++;
    }

    /** Semua pejabat: Kabag/Cabang/Unit/SPI/PAL/Staf Ahli (job_level 2) + Kepala Seksi (job_level 3). Direksi (job_level 1) sengaja tidak diikutkan. */
    public function selectAllPejabat(): void
    {
        $this->addJobLevels([2, 3]);
    }

    public function selectAllKepalaSeksi(): void
    {
        $this->addJobLevels([3]);
    }

    public function selectAllKabagCabangStaffAhli(): void
    {
        $this->addJobLevels([2]);
    }

    private function addJobLevels(array $jobLevels): void
    {
        $this->selectedUserIds = collect($this->selectedUserIds)
            ->merge(User::whereIn('job_level', $jobLevels)->pluck('id'))
            ->unique()->values()->all();

        $this->participantsVersion++;
    }

    public function clearParticipants(): void
    {
        $this->selectedUserIds = [];
        $this->bulkDepartmentIds = [];
        $this->participantsVersion++;
    }

    public function savePreset(): void
    {
        $data = $this->validate([
            'presetName' => ['required', 'string', 'max:255'],
        ]);

        $departmentMembers = $this->departmentMemberIds($this->bulkDepartmentIds);
        $extraUserIds = collect($this->selectedUserIds)->diff($departmentMembers)->values()->all();

        KpiMandatoryEventPreset::create([
            'name' => $data['presetName'],
            'department_ids' => $this->bulkDepartmentIds,
            'user_ids' => $extraUserIds,
        ]);

        $this->presetName = '';
        session()->flash('success', 'Preset peserta disimpan.');
    }

    public function loadPreset(string $presetId): void
    {
        if ($presetId === '') {
            return;
        }

        $preset = KpiMandatoryEventPreset::findOrFail((int) $presetId);

        $this->bulkDepartmentIds = $preset->department_ids ?? [];
        $this->selectedUserIds = collect($this->departmentMemberIds($this->bulkDepartmentIds))
            ->merge($preset->user_ids ?? [])
            ->unique()->values()->all();

        $this->participantsVersion++;
        session()->flash('success', "Preset \"{$preset->name}\" dimuat — sesuaikan lagi kalau perlu sebelum simpan.");
    }

    public function store(): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newDate' => ['required', 'date'],
            'newTime' => ['nullable', 'date_format:H:i'],
            'newGroupingMode' => ['required', 'in:per_bagian,gabung'],
            'selectedUserIds' => ['required', 'array', 'min:1'],
            'selectedUserIds.*' => ['exists:users,id'],
        ]);

        $event = KpiMandatoryEvent::create([
            'name' => $data['newName'],
            'date' => $data['newDate'],
            'time' => $data['newTime'] ?: null,
            'grouping_mode' => $data['newGroupingMode'],
        ]);

        $event->participants()->createMany(
            collect($data['selectedUserIds'])->map(fn ($userId) => ['user_id' => $userId])->all()
        );

        $this->reset(['newName', 'newDate', 'newTime', 'newGroupingMode', 'selectedUserIds', 'bulkDepartmentIds']);
        $this->participantsVersion++;
        $this->showCreateForm = false;
        $this->clearDateTimePickers();

        session()->flash('success', 'Kegiatan berhasil dibuat.');
    }

    /** newDate/newTime dirender via x-form.date-picker di bawah wire:ignore, jadi reset properti PHP
     * saja tidak membersihkan tampilannya — perlu dorong event supaya flatpickr ikut clear. */
    private function clearDateTimePickers(): void
    {
        $this->dispatch('datepicker-set-date', id: 'new-event-date', date: null);
        $this->dispatch('datepicker-set-date', id: 'new-event-time', date: null);
    }

    public function delete(int $eventId): void
    {
        KpiMandatoryEvent::whereKey($eventId)->delete();

        session()->flash('success', 'Kegiatan dihapus.');
    }

    /** Anggota departemen yang dipilih PLUS seluruh keturunannya (mis. Cabang -> Seksi di bawahnya). */
    private function departmentMemberIds(array $departmentIds): array
    {
        $allIds = collect($departmentIds);
        $frontier = collect($departmentIds);

        while ($frontier->isNotEmpty()) {
            $children = Department::whereIn('parent_department_id', $frontier)->pluck('id');
            $frontier = $children->diff($allIds);
            $allIds = $allIds->merge($children)->unique();
        }

        return User::whereIn('department_id', $allIds)->pluck('id')->all();
    }

    protected function sortableColumns(): array
    {
        return ['date', 'name', 'time'];
    }

    protected function defaultSort(): string
    {
        return 'date';
    }

    public function render()
    {
        return view('livewire.admin.mandatory-events-table', [
            'events' => KpiMandatoryEvent::withCount('participants')
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->perPage()),
            // Direksi sengaja dikecualikan dari pilihan departemen/cabang - lihat selectAllPejabat() dkk untuk pilihan berbasis jabatan.
            'departments' => Department::where('type', '!=', 'DIREKSI')->orderBy('name')->get(['id', 'name', 'type']),
            'nameHistory' => KpiMandatoryEvent::select('name')->distinct()->orderBy('name')->limit(50)->pluck('name'),
            'presets' => KpiMandatoryEventPreset::orderBy('name')->get(['id', 'name']),
            'allUsers' => User::orderBy('name')->get(['id', 'name', 'nik']),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}
