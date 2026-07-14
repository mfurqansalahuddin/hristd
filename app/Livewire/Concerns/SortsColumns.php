<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;

trait SortsColumns
{
    #[Url(history: true)]
    public string $sort = '';

    #[Url(history: true)]
    public string $direction = 'asc';

    public function mountSortsColumns(): void
    {
        if ($this->sort === '') {
            $this->sort = $this->defaultSort();
        }
    }

    public function sortBy(string $column, string $direction = 'asc'): void
    {
        if (! in_array($column, $this->sortableColumns(), true)) {
            return;
        }

        $this->sort = $column;
        $this->direction = $direction === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @return array<int, string>
     */
    abstract protected function sortableColumns(): array;

    protected function defaultSort(): string
    {
        return '';
    }
}
