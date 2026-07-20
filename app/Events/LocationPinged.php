<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * §15.7 poin 6 plan.md — dorong posisi terbaru ke rekan yang sedang lihat Live
 * Location, supaya tidak menunggu polling 30 detik di mobile. Channel dipecah
 * per role (bukan per departemen) untuk pejabat karena cakupannya company-wide
 * (§13); staf tetap per departemen karena cakupan mereka memang 1 departemen.
 */
class LocationPinged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public float $lat,
        public float $long,
        public string $lastUpdatedAt,
        public bool $mocked = false,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        if (in_array((int) $this->user->job_level, [1, 2, 3], true)) {
            return [new PrivateChannel('pejabat-locations')];
        }

        // Staf tanpa departemen (belum lengkap data user) tidak bisa dipetakan ke channel manapun.
        if (! $this->user->department_id) {
            return [];
        }

        return [new PrivateChannel('department-locations.'.$this->user->department_id)];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'lat' => $this->lat,
            'long' => $this->long,
            'last_updated_at' => $this->lastUpdatedAt,
            'is_online' => true,
            'is_mock_location' => $this->mocked,
        ];
    }
}
