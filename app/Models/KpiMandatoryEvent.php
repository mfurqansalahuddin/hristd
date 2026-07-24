<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiMandatoryEvent extends Model
{
    protected $fillable = ['date', 'time', 'name', 'grouping_mode', 'locked_at'];

    protected $casts = [
        'date' => 'date',
        'locked_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(KpiMandatoryEventParticipant::class);
    }
}
