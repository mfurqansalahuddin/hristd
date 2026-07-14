<?php

namespace App\Livewire\Admin;

use App\Models\KpiComponentWeight;
use App\Models\KpiIntegrityCategory;
use Livewire\Component;

class KpiCategoriesPanel extends Component
{
    public array $weights = [];

    public array $edits = [];

    public string $newName = '';

    public ?int $newDeductionValue = null;

    public function mount(): void
    {
        $this->weights = KpiComponentWeight::pluck('weight', 'id')->toArray();
        $this->syncEdits();
    }

    public function weightsTotal(): int
    {
        return array_sum($this->weights);
    }

    public function updateWeights(): void
    {
        $this->validate([
            'weights' => ['required', 'array'],
            'weights.*' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($this->weightsTotal() !== 100) {
            $this->addError('weights', 'Total bobot semua komponen harus 100%.');

            return;
        }

        foreach ($this->weights as $id => $weight) {
            KpiComponentWeight::whereKey($id)->update(['weight' => $weight]);
        }

        session()->flash('success', 'Bobot komponen KPI diperbarui.');
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

    public function updateIntegrityCategory(int $categoryId): void
    {
        $data = $this->validate([
            "edits.{$categoryId}.name" => ['required', 'string', 'max:100'],
            "edits.{$categoryId}.deduction_value" => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        KpiIntegrityCategory::whereKey($categoryId)->update($data['edits'][$categoryId]);

        session()->flash('success', 'Kategori integritas diperbarui.');
    }

    public function deleteIntegrityCategory(int $categoryId): void
    {
        KpiIntegrityCategory::whereKey($categoryId)->delete();

        unset($this->edits[$categoryId]);

        session()->flash('success', 'Kategori integritas dihapus.');
    }

    private function syncEdits(): void
    {
        $this->edits = KpiIntegrityCategory::orderBy('name')->get()
            ->mapWithKeys(fn (KpiIntegrityCategory $category) => [
                $category->id => ['name' => $category->name, 'deduction_value' => $category->deduction_value],
            ])
            ->toArray();
    }

    public function render()
    {
        $order = array_flip(array_keys(KpiComponentWeight::LABELS));

        return view('livewire.admin.kpi-categories-panel', [
            'weightRows' => KpiComponentWeight::all()->sortBy(fn (KpiComponentWeight $row) => $order[$row->component] ?? 99)->values(),
        ]);
    }
}
