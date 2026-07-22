<?php

namespace App\Livewire\Admin;

use App\Models\KpiComponentWeight;
use App\Models\KpiExtraCriterion;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiIntegritySourceWeight;
use App\Models\KpiSalaryBand;
use Livewire\Component;

class KpiCategoriesPanel extends Component
{
    public array $componentEdits = [];

    /** Snapshot terakhir tersimpan, dipakai bandingkan dirty-state per baris (server-side, bukan Alpine). */
    public array $savedComponentEdits = [];

    public array $edits = [];

    public array $savedEdits = [];

    public string $newName = '';

    public ?int $newDeductionValue = null;

    public array $sourceWeightEdits = [];

    public array $savedSourceWeightEdits = [];

    public array $bandEdits = [];

    public array $savedBandEdits = [];

    public array $criteriaEdits = [];

    public array $savedCriteria = [];

    public string $newCriterionName = '';

    public string $newCriterionDescription = '';

    public ?int $newCriterionWeight = null;

    /** Konfirmasi hapus dua-langkah via wire:click biasa — wire:confirm (dialog native browser) bisa disenyapkan browser tanpa error. */
    public ?int $confirmingCriterionId = null;

    public ?int $confirmingIntegrityId = null;

    public function mount(): void
    {
        $this->syncComponentEdits();
        $this->syncEdits();
        $this->syncCriteriaEdits();
        $this->syncSourceWeightEdits();
        $this->syncBandEdits();
    }

    public function weightsTotal(): int
    {
        return array_sum(array_column($this->componentEdits, 'weight')) + (int) KpiExtraCriterion::where('is_active', true)->sum('weight');
    }

    public function isComponentDirty(int $componentId): bool
    {
        $current = $this->componentEdits[$componentId] ?? null;
        $saved = $this->savedComponentEdits[$componentId] ?? null;

        if ($current === null || $saved === null) {
            return $current !== $saved;
        }

        return (int) $current['weight'] !== (int) $saved['weight']
            || $current['description'] !== $saved['description'];
    }

    public function updateComponentWeight(int $componentId): void
    {
        $data = $this->validate([
            "componentEdits.{$componentId}.weight" => ['required', 'integer', 'min:0', 'max:100'],
            "componentEdits.{$componentId}.description" => ['nullable', 'string', 'max:1000'],
        ]);

        KpiComponentWeight::whereKey($componentId)->update($data['componentEdits'][$componentId]);
        $this->savedComponentEdits[$componentId] = $this->componentEdits[$componentId];

        session()->flash('success', 'Bobot komponen diperbarui.');
    }

    private function syncComponentEdits(): void
    {
        $this->componentEdits = KpiComponentWeight::all()
            ->mapWithKeys(fn (KpiComponentWeight $component) => [
                $component->id => ['weight' => $component->weight, 'description' => $component->description],
            ])
            ->toArray();

        $this->savedComponentEdits = $this->componentEdits;
    }

    public function sourceWeightsTotal(): int
    {
        return array_sum(array_column($this->sourceWeightEdits, 'weight'));
    }

    public function isSourceWeightDirty(int $sourceId): bool
    {
        $current = $this->sourceWeightEdits[$sourceId] ?? null;
        $saved = $this->savedSourceWeightEdits[$sourceId] ?? null;

        if ($current === null || $saved === null) {
            return $current !== $saved;
        }

        return (int) $current['weight'] !== (int) $saved['weight'];
    }

    public function updateSourceWeight(int $sourceId): void
    {
        $data = $this->validate([
            "sourceWeightEdits.{$sourceId}.weight" => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        KpiIntegritySourceWeight::whereKey($sourceId)->update($data['sourceWeightEdits'][$sourceId]);
        $this->savedSourceWeightEdits[$sourceId] = $this->sourceWeightEdits[$sourceId];

        session()->flash('success', 'Bobot sumber integritas diperbarui.');
    }

    private function syncSourceWeightEdits(): void
    {
        $this->sourceWeightEdits = KpiIntegritySourceWeight::all()
            ->mapWithKeys(fn (KpiIntegritySourceWeight $source) => [
                $source->id => ['weight' => $source->weight],
            ])
            ->toArray();

        $this->savedSourceWeightEdits = $this->sourceWeightEdits;
    }

    /**
     * Band skor -> persentase gaji dipakai HRD, tapi cuma berlaku untuk periode BARU
     * yang belum dibuka — periode yang sudah berjalan/CLOSED pakai snapshot bekunya
     * sendiri (`KpiPeriod::weights_snapshot`), tidak ikut berubah kalau band ini diedit.
     */
    public function isBandDirty(int $bandId): bool
    {
        $current = $this->bandEdits[$bandId] ?? null;
        $saved = $this->savedBandEdits[$bandId] ?? null;

        if ($current === null || $saved === null) {
            return $current !== $saved;
        }

        return (string) $current['min_score'] !== (string) $saved['min_score']
            || (int) $current['percentage'] !== (int) $saved['percentage'];
    }

    public function updateBandWeight(int $bandId): void
    {
        $data = $this->validate([
            "bandEdits.{$bandId}.min_score" => ['nullable', 'numeric', 'min:0', 'max:100'],
            "bandEdits.{$bandId}.percentage" => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        KpiSalaryBand::whereKey($bandId)->update($data['bandEdits'][$bandId]);
        $this->savedBandEdits[$bandId] = $this->bandEdits[$bandId];

        session()->flash('success', 'Band persentase gaji diperbarui — berlaku untuk periode baru berikutnya.');
    }

    private function syncBandEdits(): void
    {
        $this->bandEdits = KpiSalaryBand::orderByDesc('min_score')->get()
            ->mapWithKeys(fn (KpiSalaryBand $band) => [
                $band->id => ['min_score' => $band->min_score, 'percentage' => $band->percentage],
            ])
            ->toArray();

        $this->savedBandEdits = $this->bandEdits;
    }

    public function storeIntegrityCategory(): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'max:100'],
            'newDeductionValue' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        KpiIntegrityCategory::create([
            'name' => $data['newName'],
            'deduction_value' => $data['newDeductionValue'],
        ]);

        $this->reset(['newName', 'newDeductionValue']);
        $this->syncEdits();

        session()->flash('success', 'Kategori integritas ditambahkan.');
    }

    public function isIntegrityDirty(int $categoryId): bool
    {
        $current = $this->edits[$categoryId] ?? null;
        $saved = $this->savedEdits[$categoryId] ?? null;

        if ($current === null || $saved === null) {
            return $current !== $saved;
        }

        return $current['name'] !== $saved['name']
            || (int) $current['deduction_value'] !== (int) $saved['deduction_value'];
    }

    public function updateIntegrityCategory(int $categoryId): void
    {
        $data = $this->validate([
            "edits.{$categoryId}.name" => ['required', 'string', 'max:100'],
            "edits.{$categoryId}.deduction_value" => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        KpiIntegrityCategory::whereKey($categoryId)->update($data['edits'][$categoryId]);
        $this->savedEdits[$categoryId] = $this->edits[$categoryId];

        session()->flash('success', 'Kategori integritas diperbarui.');
    }

    public function deleteIntegrityCategory(int $categoryId): void
    {
        KpiIntegrityCategory::whereKey($categoryId)->delete();

        unset($this->edits[$categoryId], $this->savedEdits[$categoryId]);
        $this->confirmingIntegrityId = null;

        session()->flash('success', 'Kategori integritas dihapus.');
    }

    private function syncEdits(): void
    {
        $this->edits = KpiIntegrityCategory::orderBy('name')->get()
            ->mapWithKeys(fn (KpiIntegrityCategory $category) => [
                $category->id => ['name' => $category->name, 'deduction_value' => $category->deduction_value],
            ])
            ->toArray();

        $this->savedEdits = $this->edits;
    }

    public function storeCriterion(): void
    {
        $data = $this->validate([
            'newCriterionName' => ['required', 'string', 'max:100'],
            'newCriterionDescription' => ['required', 'string', 'max:1000'],
            'newCriterionWeight' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        KpiExtraCriterion::create([
            'name' => $data['newCriterionName'],
            'description' => $data['newCriterionDescription'],
            'weight' => $data['newCriterionWeight'],
        ]);

        $this->reset(['newCriterionName', 'newCriterionDescription', 'newCriterionWeight']);
        $this->syncCriteriaEdits();

        session()->flash('success', 'Kriteria penilaian tambahan ditambahkan.');
    }

    public function isCriterionDirty(int $criterionId): bool
    {
        $current = $this->criteriaEdits[$criterionId] ?? null;
        $saved = $this->savedCriteria[$criterionId] ?? null;

        if ($current === null || $saved === null) {
            return $current !== $saved;
        }

        return $current['name'] !== $saved['name']
            || $current['description'] !== $saved['description']
            || (int) $current['weight'] !== (int) $saved['weight']
            || (bool) $current['is_active'] !== (bool) $saved['is_active'];
    }

    public function updateCriterion(int $criterionId): void
    {
        $data = $this->validate([
            "criteriaEdits.{$criterionId}.name" => ['required', 'string', 'max:100'],
            "criteriaEdits.{$criterionId}.description" => ['required', 'string', 'max:1000'],
            "criteriaEdits.{$criterionId}.weight" => ['required', 'integer', 'min:1', 'max:100'],
            "criteriaEdits.{$criterionId}.is_active" => ['required', 'boolean'],
        ]);

        KpiExtraCriterion::whereKey($criterionId)->update($data['criteriaEdits'][$criterionId]);
        $this->savedCriteria[$criterionId] = $this->criteriaEdits[$criterionId];

        session()->flash('success', 'Kriteria penilaian tambahan diperbarui.');
    }

    public function deleteCriterion(int $criterionId): void
    {
        KpiExtraCriterion::whereKey($criterionId)->delete();

        unset($this->criteriaEdits[$criterionId], $this->savedCriteria[$criterionId]);
        $this->confirmingCriterionId = null;

        session()->flash('success', 'Kriteria penilaian tambahan dihapus.');
    }

    private function syncCriteriaEdits(): void
    {
        $this->criteriaEdits = KpiExtraCriterion::orderBy('name')->get()
            ->mapWithKeys(fn (KpiExtraCriterion $criterion) => [
                $criterion->id => [
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'weight' => $criterion->weight,
                    'is_active' => $criterion->is_active,
                ],
            ])
            ->toArray();

        $this->savedCriteria = $this->criteriaEdits;
    }

    public function render()
    {
        $order = array_flip(array_keys(KpiComponentWeight::LABELS));
        $sourceOrder = array_flip(array_keys(KpiIntegritySourceWeight::LABELS));

        return view('livewire.admin.kpi-categories-panel', [
            'weightRows' => KpiComponentWeight::all()->sortBy(fn (KpiComponentWeight $row) => $order[$row->component] ?? 99)->values(),
            'sourceWeightRows' => KpiIntegritySourceWeight::all()->sortBy(fn (KpiIntegritySourceWeight $row) => $sourceOrder[$row->source] ?? 99)->values(),
            'bandRows' => KpiSalaryBand::orderByDesc('min_score')->get(),
        ]);
    }
}
