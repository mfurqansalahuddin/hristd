<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    public const JAM_MASUK_BATAS = '08:00:00';

    public const JAM_PULANG_BATAS = '16:30:00';

    public const JAM_PULANG_BATAS_SABTU = '12:00:00';

    protected $fillable = [
        'user_id', 'date', 'clock_in', 'clock_out',
        'clock_in_lat', 'clock_in_long', 'clock_out_lat', 'clock_out_long',
        'is_apel', 'status', 'late_reason', 'supervisor_approval',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'clock_in_lat' => 'decimal:8',
        'clock_in_long' => 'decimal:8',
        'clock_out_lat' => 'decimal:8',
        'clock_out_long' => 'decimal:8',
        'is_apel' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusMasuk(): ?string
    {
        if (! $this->clock_in) {
            return null;
        }

        return $this->clock_in->format('H:i:s') <= self::JAM_MASUK_BATAS ? 'TEPAT_WAKTU' : 'TERLAMBAT';
    }

    public function statusPulang(): ?string
    {
        if (! $this->clock_out) {
            return null;
        }

        $batas = $this->date?->isSaturday() ? self::JAM_PULANG_BATAS_SABTU : self::JAM_PULANG_BATAS;

        return $this->clock_out->format('H:i:s') >= $batas ? 'TEPAT_WAKTU' : 'CEPAT';
    }

    /**
     * Cari lokasi kantor (§8.1.1 plan.md) yang mencakup koordinat clock-in/out ini.
     * Null berarti di luar seluruh lokasi kantor yang terdaftar.
     */
    public function matchedLocation(string $point, Collection $locations): ?Location
    {
        $lat = $this->{"{$point}_lat"};
        $lng = $this->{"{$point}_long"};

        if ($lat === null || $lng === null) {
            return null;
        }

        return $locations->first(fn (Location $location) => $location->containsPoint((float) $lat, (float) $lng));
    }
}
