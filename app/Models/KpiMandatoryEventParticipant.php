<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiMandatoryEventParticipant extends Model
{
    protected $fillable = ['kpi_mandatory_event_id', 'user_id', 'status', 'reason', 'approval_status'];

    public function event()
    {
        return $this->belongsTo(KpiMandatoryEvent::class, 'kpi_mandatory_event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
