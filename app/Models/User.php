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
        'username',
        'name',
        'email',
        'password',
        'department_id',
        'job_level',
        'instansi',
        'employment_status',
        'leave_balance',
        'role',
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
        ];
    }

    /** SUPER_ADMIN: IT & Kepala Bagian Umum. ADMIN_KEPEGAWAIAN: HRD. STAFF (default): akses cuma mobile. */
    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    public const ROLE_ADMIN_KEPEGAWAIAN = 'ADMIN_KEPEGAWAIAN';

    public const ROLE_STAFF = 'STAFF';

    public const ROLE_LABELS = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN_KEPEGAWAIAN => 'Admin Kepegawaian',
        self::ROLE_STAFF => 'Staff (akses mobile saja)',
    ];

    public function canAccessAdminPanel(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN_KEPEGAWAIAN], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public const JOB_LEVEL_LABELS = [
        1 => 'Direksi',
        2 => 'Kepala Bagian/Cabang/Unit',
        3 => 'Kepala Seksi',
        4 => 'Staf',
    ];

    public function jobLevelLabel(): string
    {
        return self::JOB_LEVEL_LABELS[$this->job_level] ?? (string) $this->job_level;
    }

    /** Label jabatan spesifik (Kepala Bagian/Kepala Cabang/dst), diturunkan dari tipe departemen. */
    public function jabatanLabel(): string
    {
        if ((int) $this->job_level === 4) {
            return 'Staf';
        }

        return $this->department?->headLabel() ?? $this->jobLevelLabel();
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
