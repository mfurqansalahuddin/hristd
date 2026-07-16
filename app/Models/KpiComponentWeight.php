<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiComponentWeight extends Model
{
    protected $fillable = ['component', 'weight', 'description'];

    public const LABELS = [
        'KINERJA' => 'Kinerja Teknis',
        'KEHADIRAN' => 'Kehadiran',
        'APEL' => 'Apel Pagi',
        'PAKAIAN_DINAS' => 'Pakaian Dinas',
        'INTEGRITAS' => 'Integritas',
    ];

    public function label(): string
    {
        return self::LABELS[$this->component] ?? $this->component;
    }
}
