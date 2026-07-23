<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiMandatoryEvent extends Model
{
    protected $fillable = ['date', 'time', 'name', 'grouping_mode'];

    protected $casts = [
        'date' => 'date',
    ];

    public function participants()
    {
        return $this->hasMany(KpiMandatoryEventParticipant::class);
    }
}
