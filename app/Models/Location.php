<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'type', 'lat', 'long', 'radius_meters', 'polygon'];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:8',
            'long' => 'decimal:8',
            'polygon' => 'array',
        ];
    }

    /**
     * Semua lokasi kantor dalam bentuk siap gambar di minimap (Home + Live Location).
     *
     * @return array<int, array{id: int, name: string, type: string, lat: float, long: float, radius_meters: ?int, polygon: ?array}>
     */
    public static function forMinimap(): array
    {
        return static::all(['id', 'name', 'type', 'lat', 'long', 'radius_meters', 'polygon'])
            ->map(fn (self $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'type' => $location->type,
                'lat' => (float) $location->lat,
                'long' => (float) $location->long,
                'radius_meters' => $location->radius_meters,
                'polygon' => $location->polygon,
            ])
            ->all();
    }

    /**
     * Cek apakah sebuah titik koordinat berada di dalam area lokasi ini (§8.1.1 plan.md).
     */
    public function containsPoint(float $lat, float $lng): bool
    {
        if ($this->type === 'POLYGON' && $this->polygon) {
            return $this->pointInPolygon($lat, $lng, $this->polygon);
        }

        return $this->haversineMeters((float) $this->lat, (float) $this->long, $lat, $lng) <= ($this->radius_meters ?? 0);
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $polygon
     */
    private function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) $polygon[$i]['lat'];
            $yi = (float) $polygon[$i]['lng'];
            $xj = (float) $polygon[$j]['lat'];
            $yj = (float) $polygon[$j]['lng'];

            $intersect = (($yi > $lng) !== ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
