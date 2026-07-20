<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    public const TYPE_CUTI = 'CUTI';

    public const TYPE_SAKIT = 'SAKIT';

    public const TYPE_IZIN = 'IZIN';

    public const TYPE_DINAS_LUAR = 'DINAS_LUAR';

    public const SOURCE_APP = 'APP';

    public const SOURCE_HR_MANUAL = 'HR_MANUAL';

    protected $fillable = [
        'user_id', 'type', 'start_date', 'end_date', 'reason',
        'attachment_path', 'status', 'rejection_reason', 'approved_by_id',
        'source', 'dl_batch_uuid',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
