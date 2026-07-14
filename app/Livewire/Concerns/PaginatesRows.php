<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;

trait PaginatesRows
{
    const PER_PAGE_OPTIONS = [5, 10, 20, 50];

    #[Url(history: true)]
    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function perPage(): int
    {
        return in_array($this->perPage, self::PER_PAGE_OPTIONS, true) ? $this->perPage : 10;
    }
}
