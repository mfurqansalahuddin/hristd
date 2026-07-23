<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\KpiMandatoryEvent;
use App\Models\KpiMandatoryEventParticipant;
use Livewire\Component;

class MandatoryEventAttendanceForm extends Component
{
    public KpiMandatoryEvent $event;

    /** @var array<int, array{status: ?string, reason: ?string}> */
    public array $edits = [];

    /** @var array<int, array{status: ?string, reason: ?string}> */
    public array $savedEdits = [];

    public string $search = '';

    public string $filterStatus = 'semua';

    /** Alasan yang sudah tersimpan & bersih ditampilkan terkunci (ringkas) sampai user klik "Ubah" - lihat isReasonEditing(). */
    public array $reasonEditing = [];

    public function mount(KpiMandatoryEvent $event): void
    {
        $this->event = $event;
        $this->syncEdits();
    }

    public function isDirty(int $participantId): bool
    {
        return ($this->edits[$participantId] ?? null) !== ($this->savedEdits[$participantId] ?? null);
    }

    public function save(int $participantId): void
    {
        $data = $this->validate([
            "edits.{$participantId}.status" => ['required', 'string', 'in:HADIR,TELAT,TIDAK_HADIR'],
            "edits.{$participantId}.reason" => ['nullable', 'string', 'max:1000', 'required_if:edits.'.$participantId.'.status,TIDAK_HADIR'],
        ])['edits'][$participantId];

        $participant = KpiMandatoryEventParticipant::findOrFail($participantId);
        $hasReason = filled($data['reason'] ?? null);

        $participant->update([
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            // Reason (wajib utk Tidak Hadir, opsional/anotasi utk Telat) selalu masuk antrean approval HR.
            // Untuk Telat, approval TIDAK menggerbang skor (lihat KpiEvaluationService::apelScore) — murni dokumentasi.
            'approval_status' => $hasReason ? ($participant->approval_status ?: 'PENDING') : null,
        ]);

        $this->savedEdits[$participantId] = $this->edits[$participantId];
        unset($this->reasonEditing[$participantId]);

        session()->flash('success', 'Presensi tersimpan.');
    }

    /** Buka lagi kotak alasan yang sudah terkunci (sudah tersimpan & bersih) supaya bisa diubah tanpa mengganti status. */
    public function editReason(int $participantId): void
    {
        $this->reasonEditing[$participantId] = true;
    }

    /** Kotak alasan tampil terbuka selagi: baru diklik "Ubah", ada perubahan belum tersimpan, atau memang belum pernah diisi. */
    public function isReasonEditing(KpiMandatoryEventParticipant $participant): bool
    {
        return ($this->reasonEditing[$participant->id] ?? false)
            || $this->isDirty($participant->id)
            || blank($participant->reason);
    }

    public function approve(int $participantId): void
    {
        KpiMandatoryEventParticipant::whereKey($participantId)->update(['approval_status' => 'APPROVED']);

        session()->flash('success', 'Alasan disetujui.');
    }

    public function reject(int $participantId): void
    {
        KpiMandatoryEventParticipant::whereKey($participantId)->update(['approval_status' => 'REJECTED']);

        session()->flash('success', 'Alasan ditolak.');
    }

    private function syncEdits(): void
    {
        $this->edits = $this->event->participants()->get()
            ->mapWithKeys(fn (KpiMandatoryEventParticipant $p) => [$p->id => ['status' => $p->status, 'reason' => $p->reason]])
            ->toArray();

        $this->savedEdits = $this->edits;
    }

    /** Pejabat (job_level 1-3: Direksi/Kabag/Cabang/Kasi/dst) ditampilkan terpisah dari staf - lihat render(). */
    private function isPejabat(KpiMandatoryEventParticipant $participant): bool
    {
        return (int) ($participant->user?->job_level ?? 4) < 4;
    }

    /** Bagian/cabang tempat staf dikelompokkan: kalau departemen staf itu Seksi, naik ke induknya (Bagian/Cabang). */
    private function staffUnit(KpiMandatoryEventParticipant $participant): ?Department
    {
        $department = $participant->user?->department;

        if (! $department) {
            return null;
        }

        return $department->type === 'SEKSI' ? $department->parent : $department;
    }

    /** Ringkasan Hadir/Telat/Tidak Hadir untuk satu kelompok (Pejabat atau satu Bagian/Cabang) - dipakai di header kartu kelompok. */
    private function tally(\Illuminate\Support\Collection $people): array
    {
        $counts = ['hadir' => 0, 'telat' => 0, 'tidak_hadir' => 0];

        foreach ($people as $p) {
            match ($this->edits[$p->id]['status'] ?? null) {
                'HADIR' => $counts['hadir']++,
                'TELAT' => $counts['telat']++,
                'TIDAK_HADIR' => $counts['tidak_hadir']++,
                default => null,
            };
        }

        return $counts;
    }

    public function render()
    {
        $all = $this->event->participants()->with('user.department.parent')->get();

        $summary = ['hadir' => 0, 'telat' => 0, 'tidak_hadir' => 0, 'belum' => 0, 'perlu_approval' => 0];

        foreach ($all as $p) {
            $status = $this->edits[$p->id]['status'] ?? null;

            match ($status) {
                'HADIR' => $summary['hadir']++,
                'TELAT' => $summary['telat']++,
                'TIDAK_HADIR' => $summary['tidak_hadir']++,
                default => $summary['belum']++,
            };

            if ($p->approval_status === 'PENDING') {
                $summary['perlu_approval']++;
            }
        }

        $search = mb_strtolower(trim($this->search));

        $filtered = $all->filter(function (KpiMandatoryEventParticipant $p) use ($search) {
            if ($search !== '') {
                $haystack = mb_strtolower(($p->user?->name ?? '').' '.($p->user?->nik ?? ''));

                if (! str_contains($haystack, $search)) {
                    return false;
                }
            }

            $status = $this->edits[$p->id]['status'] ?? null;

            return match ($this->filterStatus) {
                'hadir' => $status === 'HADIR',
                'telat' => $status === 'TELAT',
                'tidak_hadir' => $status === 'TIDAK_HADIR',
                'belum' => $status === null,
                default => true,
            };
        });

        if ($this->event->grouping_mode === 'gabung') {
            $leaders = collect();

            $staffGroups = collect([[
                'label' => 'Semua Peserta',
                'people' => $filtered->sortBy(fn ($p) => $p->user?->name)->values(),
                'summary' => $this->tally($filtered),
            ]]);
        } else {
            $leaders = $filtered->filter(fn ($p) => $this->isPejabat($p))
                ->sortBy(fn ($p) => sprintf('%02d-%s', $p->user?->job_level ?? 9, $p->user?->name ?? ''))
                ->values();

            $staffGroups = $filtered->reject(fn ($p) => $this->isPejabat($p))
                ->groupBy(fn ($p) => $this->staffUnit($p)?->id ?? 0)
                ->map(function ($people) {
                    $unit = $this->staffUnit($people->first());

                    return [
                        'label' => $unit?->name ?? 'Tanpa Bagian',
                        'people' => $people->sortBy(fn ($p) => $p->user?->name)->values(),
                        'summary' => $this->tally($people),
                    ];
                })
                ->sortBy('label')
                ->values();
        }

        return view('livewire.admin.mandatory-event-attendance-form', [
            'summary' => $summary,
            'totalCount' => $all->count(),
            'leaders' => $leaders,
            'leadersSummary' => $this->tally($leaders),
            'staffGroups' => $staffGroups,
            'noResults' => $filtered->isEmpty(),
        ]);
    }
}
