<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Data pejabat riil Perumdam Tirta Daroy (Direksi s.d. Kepala Seksi) — dihardcode
 * di sini karena sempat hilang beberapa kali akibat migrate:fresh. Password semua
 * user hasil seeder ini adalah "password" (default UserFactory), silakan diganti
 * manual lewat form pegawai.
 *
 * Bukan Seeder biasa (tidak dipanggil lewat $this->call), karena butuh Department
 * yang sudah dibuat lebih dulu oleh DatabaseSeeder. Dipakai lewat makeUser() per
 * Department. Key array = nama Department persis seperti di DatabaseSeeder::$bagianDefs
 * / daftar seksi; value = [nama, nik]. Seksi Laboratorium sengaja tidak didaftarkan
 * (belum ada datanya) supaya fallback ke factory random.
 */
class RealEmployeeSeeder
{
    /** @var array<string, array{0: string, 1: ?string}> nama Direksi => [nama, nik] */
    public const REAL_DIREKSI = [
        'Direktur Utama' => ['T. Novizal Aiyub, SE.Ak', null],
        'Direktur ADM & Keuangan' => ['Samirul Fuadi, SE', null],
        'Direktur Teknik' => ['Irwandi, ST, MT', null],
    ];

    /** @var array<string, array{0: string, 1: string}> nama Bagian/Cabang/Unit/SPI/Staf Ahli => [nama, nik] */
    public const REAL_LEADERS = [
        'Cabang Teuku Nyak Arief' => ['Tarmizi Ibnu', '158'],
        'Staf Ahli Bidang Teknik' => ['T. Raja Waris, S.Sos', '187'],
        'Bagian Transmisi dan Distribusi' => ['Saiful Yunus', '207'],
        'Bagian Produksi' => ['Fakri S.', '211'],
        'Cabang Syiah Kuala' => ['Khaibar Abrar', '212'],
        'Cabang Teuku Umar' => ['Azhari', '214'],
        'Bagian Perencanaan Teknik dan Pengawasan Teknik' => ['Mulyadi M. Daud', '220'],
        'Staf Ahli Bidang Administrasi' => ['Ir. Nasrizal Nasa, ST, MT', '221'],
        'Satuan Pengawas Internal (SPI)' => ['Yusmadi', '222'],
        'Bagian PAL (Pengolahan Air Limbah)' => ['Zidni Sesilia, SE', '233'],
        'Unit Teknologi Informasi' => ['Munawir Abdul Aziz', '235'],
        'Cabang Sultan Iskandar Muda' => ['Fitriadi, S.TP', '241'],
        'Bagian Keuangan' => ['Elmida, SE', '243'],
        'Bagian Hubungan Pelanggan' => ['Tarmizi, SE', '256'],
        'Bagian Umum' => ['Hendra Alhas, ST, MT', '317'],
    ];

    /** @var array<string, array{0: string, 1: string}> nama Seksi => [nama, nik] */
    public const REAL_KASI = [
        'Seksi Anggaran' => ['Dian Lailan Maulida, SE', '304'],
        'Seksi Kas/Gaji' => ['Mirza Rianda, A.Md', '269'],
        'Seksi Akuntansi' => ['Desrinawati, SE', '253'],
        'Seksi Kepegawaian dan Hukum' => ['Raihanil Jannah, SE', '409'],
        'Seksi Sekretariat dan ADM' => ['Zahrani Balkis', '395'],
        'Seksi Gudang' => ['Muslim', '242'],
        'Seksi Perlengkapan' => ['Zulfan, ST', '262'],
        'Seksi Pelayanan Pelanggan' => ['Muliyadi', '223'],
        'Seksi Pembaca Meter' => ['Ivan Daryansyah Bustamam, SE', '237'],
        'Seksi Rekening' => ['Muhammad Syafrizal, ST', '332'],
        'Seksi Perencanaan Teknik' => ['Junaidi, ST', '335'],
        'Seksi Pengawasan Teknik' => ['Edi Kurniawan, ST', '324'],
        'Seksi Operasi' => ['Andi Suhendra, ST', '312'],
        'Seksi Pemeliharaan' => ['Faudhal Akbar, ST', '313'],
        'Seksi Sistem Pendistribusian Air' => ['Syafrizal', '258'],
        'Seksi Penanganan Kebocoran & Pengendalian Kehilangan Air' => ['Rahmat Fuzir Faluthfi, ST', '259'],
        'Seksi Pengawasan Bidang Teknik' => ['Asrun', '226'],
        'Seksi Pengawasan Bidang Umum & Keuangan' => ['Kiki Riski, S.Sos', '410'],
        'Seksi Adm Cabang Teuku Nyak Arief' => ['Yusneti, SE', '196'],
        'Seksi Teknik Cabang Teuku Nyak Arief' => ['Husaini', '248'],
        'Seksi Adm Cabang Sultan Iskandar Muda' => ['Wahyuna Fitri', '199'],
        'Seksi Teknik Cabang Sultan Iskandar Muda' => ['T. Fauzan Aziman, S.Kom', '340'],
        'Seksi Adm Cabang Teuku Umar' => ['Cut Fajriani Gustiana, ST', '282'],
        'Seksi Teknik Cabang Teuku Umar' => ['Subhan', '310'],
        'Seksi Adm Cabang Syiah Kuala' => ['Junaidi', '247'],
        'Seksi Teknik Cabang Syiah Kuala' => ['Sanusi S.T.', '239'],
        'Seksi ADM Bagian Pengolahan Air Limbah' => ['Muhammad Juaini, SE', '168'],
        'Seksi Teknik Bagian Pengolahan Air Limbah' => ['T. Putrawan Jaya, ST', '284'],
    ];

    /** Nama Department yang pejabatnya dapat role SUPER_ADMIN (IT & Kepala Bagian Umum, lihat migration role). */
    public const SUPER_ADMIN_DEPARTMENTS = ['Unit Teknologi Informasi', 'Bagian Umum'];

    /**
     * Buat user untuk sebuah Department. Kalau nama Department-nya ada di
     * REAL_DIREKSI/REAL_LEADERS/REAL_KASI dipakai data riilnya (username/email
     * diturunkan dari nama), kalau tidak fallback ke factory random.
     */
    public function makeUser(Department $department, int $jobLevel, ?string $role = null): User
    {
        $real = self::REAL_DIREKSI[$department->name]
            ?? self::REAL_LEADERS[$department->name]
            ?? self::REAL_KASI[$department->name]
            ?? null;

        $overrides = ['department_id' => $department->id, 'job_level' => $jobLevel];

        $role ??= in_array($department->name, self::SUPER_ADMIN_DEPARTMENTS, true) ? 'SUPER_ADMIN' : null;
        if ($role) {
            $overrides['role'] = $role;
        }

        if ($real) {
            [$name, $nik] = $real;
            $username = Str::slug($name, '.');
            $overrides['name'] = $name;
            $overrides['username'] = $username;
            $overrides['email'] = "{$username}@tirtadaroy.id";
            $overrides['nik'] = $nik ?? $username;
        }

        return User::factory()->create($overrides);
    }
}
