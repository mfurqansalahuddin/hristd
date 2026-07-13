<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'name',
        'email',
        'password',
        'department_id',
        'job_level',
        'direct_supervisor_id',
        'final_supervisor_id',
        'instansi',
        'employment_status',
        'leave_balance',
        'is_admin',
        'photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function photoUrl(): string
    {
        return $this->photo_path
            ? asset('storage/'.$this->photo_path)
            : 'data:image/svg+xml;base64,'.base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" rx="20" fill="#465fff"/><text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="sans-serif" font-size="16" fill="#fff">'.strtoupper(substr($this->name ?? '?', 0, 1)).'</text></svg>'
            );
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function directSupervisor()
    {
        return $this->belongsTo(User::class, 'direct_supervisor_id');
    }

    public function finalSupervisor()
    {
        return $this->belongsTo(User::class, 'final_supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'direct_supervisor_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function currentLocation()
    {
        return $this->hasOne(UserCurrentLocation::class);
    }

    public function kpiPlans()
    {
        return $this->hasMany(KpiPlan::class);
    }

    public function dailyActivities()
    {
        return $this->hasMany(DailyActivity::class);
    }
}
