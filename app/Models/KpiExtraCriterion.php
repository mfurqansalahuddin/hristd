<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiExtraCriterion extends Model
{
    protected $fillable = ['name', 'description', 'weight', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scores()
    {
        return $this->hasMany(KpiExtraCriterionScore::class);
    }
}
