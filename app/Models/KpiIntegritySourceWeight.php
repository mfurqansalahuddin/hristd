<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiIntegritySourceWeight extends Model
{
    protected $fillable = ['source', 'weight'];

    public const LABELS = [
        'PENILAI_1' => 'Penilai 1 (Atasan Langsung)',
        'PENILAI_2' => 'Penilai 2 (Atasan dari Atasan)',
        'PENILAI_3' => 'Rekan Seksi (Acak)',
        'ADUAN_PERUSAHAAN' => 'Aduan Perusahaan (Company-wide)',
    ];

    public function label(): string
    {
        return self::LABELS[$this->source] ?? $this->source;
    }
}
