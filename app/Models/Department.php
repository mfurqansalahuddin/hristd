<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'type', 'parent_department_id', 'directorate'];

    /** Tipe departemen yang valid dipilih untuk tiap job_level pegawai (§6 plan.md). */
    public const JOB_LEVEL_TYPES = [
        1 => ['DIREKSI'],
        2 => ['BAGIAN', 'CABANG', 'UNIT', 'SPI', 'PAL', 'STAF_AHLI'],
        3 => ['SEKSI'],
        4 => ['SEKSI', 'UNIT'],
    ];

    /** Label jabatan spesifik per tipe departemen, dipakai di form pegawai & Manajemen Jabatan. */
    public const TYPE_LABELS = [
        'DIREKSI' => 'Direktur',
        'BAGIAN' => 'Kepala Bagian',
        'CABANG' => 'Kepala Cabang',
        'UNIT' => 'Kepala Unit',
        'SPI' => 'Kepala SPI',
        'PAL' => 'Kepala Bagian PAL',
        'STAF_AHLI' => 'Staf Ahli',
        'SEKSI' => 'Kepala Seksi',
    ];

    /** Tipe departemen yang bisa ditambah lewat "Tambah Jabatan" (Direksi tidak, sudah tetap 3 orang). */
    public const CREATABLE_TYPES = ['BAGIAN', 'CABANG', 'UNIT', 'SPI', 'PAL', 'STAF_AHLI', 'SEKSI'];

    /** Tipe induk yang valid untuk tiap tipe departemen baru (kebalikan dari JOB_LEVEL_TYPES, §3.3 plan.md). */
    public const PARENT_TYPES = [
        'BAGIAN' => ['DIREKSI'],
        'CABANG' => ['DIREKSI'],
        'UNIT' => ['DIREKSI'],
        'SPI' => ['DIREKSI'],
        'PAL' => ['DIREKSI'],
        'STAF_AHLI' => ['DIREKSI'],
        'SEKSI' => ['BAGIAN', 'CABANG', 'SPI', 'PAL'],
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    /** job_level seorang kepala/pejabat di node ini (dipakai Manajemen Jabatan). */
    public function headJobLevel(): int
    {
        return match ($this->type) {
            'DIREKSI' => 1,
            'SEKSI' => 3,
            default => 2,
        };
    }

    public function headLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
