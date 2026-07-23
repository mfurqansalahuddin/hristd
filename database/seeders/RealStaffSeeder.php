<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Data staf (job_level 4) riil Perumdam Tirta Daroy — dihardcode dari roster asli
 * (Stafftd.txt) supaya tidak hilang lagi akibat migrate:fresh, sama seperti
 * RealEmployeeSeeder untuk Direksi/Kabag/Kasi.
 *
 * Bukan Seeder biasa (tidak dipanggil lewat $this->call), dipakai lewat
 * makeUsersFor() per Department dari DatabaseSeeder. Key array = nama Department
 * persis seperti di DatabaseSeeder::$bagianDefs / daftar seksi. Seksi Pengawasan
 * Bidang Teknik (SPI) dan kedua Staf Ahli sengaja tidak didaftarkan (tidak ada
 * staf di roster) supaya fallback ke factory random.
 *
 * NIK: nomor asli dipakai apa adanya. Untuk yang di roster cuma tertulis status
 * ("Kontrak"/"Magang", tanpa nomor), dipakai kode sintetis berurutan mulai dari
 * 10 — Kontrak dulu semua (10-25, urut sesuai baris di roster), baru Magang
 * (26-38), lalu Pramagang kalau ada nanti (tidak ada di roster ini). Aman dari
 * tumpang tindih karena NIK asli semuanya 3 digit (>=158).
 */
class RealStaffSeeder
{
    /** @var array<string, list<array{0: string, 1: string, 2: string}>> nama Department => [nama, nik, employment_status] */
    private const REAL_STAFF = [
        'Seksi Anggaran' => [
            ['Pasrah, SE', '303', 'TETAP'],
            ['Naziratul Ula, S.T', '435', 'TETAP'],
        ],
        'Seksi Kas/Gaji' => [
            ['Rini Kartika Ayu', '234', 'TETAP'],
            ['Nurhayana Suhaila, A.Md', '318', 'TETAP'],
            ['Cici Vidya Saila, SE', '418', 'TETAP'],
            ['Nur Afdina Utami', '425', 'TETAP'],
        ],
        'Seksi Akuntansi' => [
            ['Rizki Rahmawati, SE', '374', 'TETAP'],
            ['Hasnawati, SE', '433', 'TETAP'],
            ['Farah Zayyan S. Sos', '26', 'MAGANG'],
        ],
        'Seksi Kepegawaian dan Hukum' => [
            ['Herlinawati', '205', 'TETAP'],
            ['Elvia Oktayani, SE', '270', 'TETAP'],
            ['Andi Ismayadi, SE', '280', 'TETAP'],
        ],
        'Seksi Sekretariat dan ADM' => [
            ['Amir', '228', 'TETAP'],
            ['Abdus Salam', '298', 'TETAP'],
            ['Mustafa', '356', 'TETAP'],
            ['Nurul Akhmal, SE', '369', 'TETAP'],
            ['Muhammad Rizal', '411', 'TETAP'],
            ['Maskur', '419', 'TETAP'],
            ['Reza Saputra', '423', 'TETAP'],
            ['Mouliza Astari, S.Pd', '426', 'TETAP'],
            ['Jihan Zahira, SE', '10', 'KONTRAK'],
            ['Aulia Salsabila, S.Pd', '11', 'KONTRAK'],
            ['Mohd. Furqan Salahuddin, SE', '27', 'MAGANG'],
        ],
        'Seksi Gudang' => [
            ['Yenni Afriany, SE', '286', 'TETAP'],
            ['Muhammad Zhuhri', '362', 'TETAP'],
            ['Astrid Maulida Rizki, A. Md', '383', 'TETAP'],
            ['Muhammad Ikhsan, SH', '12', 'KONTRAK'],
        ],
        'Seksi Perlengkapan' => [
            ['Hasniati, SE', '236', 'TETAP'],
            ['Yunika Maulidia, S.Pd', '439', 'TETAP'],
        ],
        'Seksi Pelayanan Pelanggan' => [
            ['T. Raiyan, SE', '417', 'TETAP'],
        ],
        'Seksi Pembaca Meter' => [
            ['Siti Azmatun', '408', 'TETAP'],
        ],
        'Seksi Rekening' => [
            ['Havidh Muarief, SE', '430', 'TETAP'],
            ['Dhaifina Rizki Damelia, SH', '28', 'MAGANG'],
            ['Fathur Haykal Rosdi', '29', 'MAGANG'],
        ],
        'Seksi Perencanaan Teknik' => [
            ['Chandra Morgana, S.KH.', '378', 'TETAP'],
            ['Muhariz Azmi, ST', '404', 'TETAP'],
            ['Ilham Ramadhan, ST', '413', 'TETAP'],
            ['Raihan Nadia, SE', '13', 'KONTRAK'],
            ['M. Yudhi Setiawan, ST', '14', 'KONTRAK'],
            ['Tengku Siti Fatimah, ST', '30', 'MAGANG'],
        ],
        'Seksi Pengawasan Teknik' => [
            ['Rahmat Affandi, SE', '339', 'TETAP'],
            ['Al Hafidh Muttaqin, SH', '399', 'TETAP'],
        ],
        'Seksi Operasi' => [
            ['Cut Ahmad Suluki', '178', 'TETAP'],
            ['Hasballah', '276', 'TETAP'],
            ['Munawir', '296', 'TETAP'],
            ['Denny Andrika', '300', 'TETAP'],
            ['Rahmat', '311', 'TETAP'],
            ['Akbar Rizal', '331', 'TETAP'],
            ['Risnandar, A.Md', '343', 'TETAP'],
            ['Dedi Warni', '346', 'TETAP'],
            ['Jusin', '349', 'TETAP'],
            ['Helmi Saputra', '391', 'TETAP'],
            ['Asmar Rahmatillah', '392', 'TETAP'],
            ['Mirza Zulyandi, S.P', '403', 'TETAP'],
            ['Putra Mulya Syukur, S.T', '429', 'TETAP'],
            ['Alfayed Aqsha', '15', 'KONTRAK'],
            ['Habibi', '16', 'KONTRAK'],
            ['Raisul Sidik', '31', 'MAGANG'],
        ],
        'Seksi Laboratorium' => [
            ['Samsul Bahri, ST', '271', 'TETAP'],
            ['Mukhlis', '275', 'TETAP'],
            ['Siti Zulhijjah', '323', 'TETAP'],
        ],
        'Seksi Pemeliharaan' => [
            ['Khairil Umry, A. Md', '389', 'TETAP'],
            ['Sibratullah', '396', 'TETAP'],
            ['Muhammad Aulia', '415', 'TETAP'],
            ['Muhammad Tesar', '432', 'TETAP'],
            ['M. Fadhil Alwi, SE', '434', 'TETAP'],
            ['Rahmat Sahputra, S.sos', '17', 'KONTRAK'],
        ],
        'Seksi Sistem Pendistribusian Air' => [
            ['Ramadhan, SE', '250', 'TETAP'],
            ['Sandi Suryadi', '278', 'TETAP'],
            ['Teuku M. Ghufran Ilhamy, SE', '437', 'TETAP'],
            ['Rizki Rinaldi', '444', 'TETAP'],
        ],
        'Seksi Penanganan Kebocoran & Pengendalian Kehilangan Air' => [
            ['Zhafirul Hanif, A.Md', '330', 'TETAP'],
            ['Zawil Qurba', '393', 'TETAP'],
        ],
        'Seksi Pengawasan Bidang Umum & Keuangan' => [
            ['Fazila Azzuhra, SH', '440', 'TETAP'],
        ],
        'Unit Teknologi Informasi' => [
            ['Arief Rahman, S.Kom', '428', 'TETAP'],
        ],
        'Seksi Adm Cabang Teuku Nyak Arief' => [
            ['Ade Rahmawati', '183', 'TETAP'],
            ['Arjunawati', '216', 'TETAP'],
            ['Tina Lidadari', '245', 'TETAP'],
            ['Mirta Wulansari, SE', '272', 'TETAP'],
            ['Hermawati', '249', 'TETAP'],
            ['Sarianita, S.Si', '327', 'TETAP'],
            ['Chartika Permata Harahap, SKM', '328', 'TETAP'],
            ['Herman Sanopha', '352', 'TETAP'],
            ['Nasri', '355', 'TETAP'],
            ['Sulaiman AB', '357', 'TETAP'],
            ['Fajri', '363', 'TETAP'],
            ['Hendri', '370', 'TETAP'],
            ['Rusydi Zakaria, S.Pd.I', '377', 'TETAP'],
            ['Fiza Ferdian', '390', 'TETAP'],
            ['Rofiana, SE', '397', 'TETAP'],
            ['Syaskia Hildayani, A.Md. Kes', '420', 'TETAP'],
            ['Siti Rania Arida, SE', '442', 'TETAP'],
            ['Al-Hadid Qursani', '18', 'KONTRAK'],
            ['Raisha Alifia Chairan, SH', '32', 'MAGANG'],
        ],
        'Seksi Teknik Cabang Teuku Nyak Arief' => [
            ['Mardanil', '255', 'TETAP'],
            ['Iswadi', '265', 'TETAP'],
            ['Syahrial Sahputra', '302', 'TETAP'],
            ['Syahrul Mubaraq', '321', 'TETAP'],
            ['Fajar Juliadi', '325', 'TETAP'],
            ['T. Verri Afriadi', '338', 'TETAP'],
            ['Dodi Rilantember', '359', 'TETAP'],
            ['Muhammad Aulia', '402', 'TETAP'],
        ],
        'Seksi Adm Cabang Sultan Iskandar Muda' => [
            ['Mahdarlena', '292', 'TETAP'],
            ['Novizar, A.Md', '342', 'TETAP'],
            ['Fitri Aprilia', '344', 'TETAP'],
            ['Juniardi', '348', 'TETAP'],
            ['Anshar', '371', 'TETAP'],
            ['Zulham', '373', 'TETAP'],
            ['Dessy Dora Karewur, A. Md', '387', 'TETAP'],
            ['Rohimah', '405', 'TETAP'],
            ['Khairunnas', '427', 'TETAP'],
            ['Diga Septianto, SE', '441', 'TETAP'],
            ['Tania Humaira, A.Md', '19', 'KONTRAK'],
            ['Melisa Rohaya', '33', 'MAGANG'],
            ['Muhammad Fariz Akbar', '34', 'MAGANG'],
        ],
        'Seksi Teknik Cabang Sultan Iskandar Muda' => [
            ['Saiful Ramadhan, A.Md', '238', 'TETAP'],
            ['Faisal', '254', 'TETAP'],
            ['Irwan', '260', 'TETAP'],
            ['Ahmad Khuwaini', '261', 'TETAP'],
            ['Saiful Amri', '279', 'TETAP'],
            ['Adi Saputra', '290', 'TETAP'],
            ['Zamzami, A.Md', '293', 'TETAP'],
            ['Faisal', '295', 'TETAP'],
            ['Safriadi, ST', '299', 'TETAP'],
            ['Gunawan', '308', 'TETAP'],
            ['Mohd. Ricky, SE', '319', 'TETAP'],
            ['Busra Abizar', '309', 'TETAP'],
            ['Azwar Abdullah', '333', 'TETAP'],
            ['Wahyu Supiagung', '347', 'TETAP'],
            ['Riza Fitriadi', '351', 'TETAP'],
            ['Zulham Efendi, A.Md', '372', 'TETAP'],
            ['Andi Yansah', '406', 'TETAP'],
            ['Ruslan, SE', '412', 'TETAP'],
            ['Reza Fahlevi', '416', 'TETAP'],
            ['Muhammad Syawal Afif', '35', 'MAGANG'],
        ],
        'Seksi Adm Cabang Teuku Umar' => [
            ['Nuzulia, SE', '266', 'TETAP'],
            ['Khairul Dani', '267', 'TETAP'],
            ['Fauzi', '288', 'TETAP'],
            ['Vera Yulita, A.Md', '289', 'TETAP'],
            ['Anggy Muharami', '291', 'TETAP'],
            ['Devi Juliana Sari, S.TP', '320', 'TETAP'],
            ['Linda Wardany, SE', '326', 'TETAP'],
            ['Zaimi Novrizal, ST', '336', 'TETAP'],
            ['Cut Nisa Chairun LQ, ST', '341', 'TETAP'],
            ['Aidul Azhari', '350', 'TETAP'],
            ['Anwar', '354', 'TETAP'],
            ['Syakubat', '365', 'TETAP'],
            ['Saiful', '366', 'TETAP'],
            ['Muhammad', '367', 'TETAP'],
            ['Cut Maghfirah, SH', '385', 'TETAP'],
            ['Munadiatul Khairiah, S. Pd', '398', 'TETAP'],
            ['Meliza Gharsina, A.Md', '424', 'TETAP'],
            ['Suraiya Husna, SE', '20', 'KONTRAK'],
            ['Lukman Nulhakim', '36', 'MAGANG'],
        ],
        'Seksi Teknik Cabang Teuku Umar' => [
            ['Zulkifli', '224', 'TETAP'],
            ['Yudi Wisnu Bermana, SE', '257', 'TETAP'],
            ['Muhammad Husni', '273', 'TETAP'],
            ['Fahrullah', '274', 'TETAP'],
            ['Rahmat Yani', '277', 'TETAP'],
            ['Suriadi. S', '358', 'TETAP'],
            ['Heriansyah', '361', 'TETAP'],
            ['Bahrun Muhib', '364', 'TETAP'],
            ['Garil Phonna, SH', '375', 'TETAP'],
            ['Yuni Bernanda Saputra, SE', '376', 'TETAP'],
            ['Ahmad Afdhal, S.Pd', '381', 'TETAP'],
            ['Naftian Urfan', '400', 'TETAP'],
            ['Agus Suhendra, A.Md', '37', 'MAGANG'],
        ],
        'Seksi Adm Cabang Syiah Kuala' => [
            ['Zakiah', '246', 'TETAP'],
            ['Marliana, A.Md', '251', 'TETAP'],
            ['Muammar Riza, A.Md', '252', 'TETAP'],
            ['Hasanusi, SE', '263', 'TETAP'],
            ['Nita Fitriyani', '285', 'TETAP'],
            ['Pipit Hanifa Mutia, S.Si', '305', 'TETAP'],
            ['Asrina, SKM', '316', 'TETAP'],
            ['Oktaviana, SE', '329', 'TETAP'],
            ['Agus Mawardi, SH', '368', 'TETAP'],
            ['Mujahidat', '379', 'TETAP'],
            ['M. Jabir', '394', 'TETAP'],
            ['Ichlasul Fikri', '421', 'TETAP'],
            ['Yusuf Ridha Septian', '422', 'TETAP'],
            ['Zulham', '436', 'TETAP'],
            ['Shuvia Baitil Askya', '443', 'TETAP'],
            ['Sri Rahayu Utami', '21', 'KONTRAK'],
            ['Putroe Elisa S.Mat', '22', 'KONTRAK'],
        ],
        'Seksi Teknik Cabang Syiah Kuala' => [
            ['Muhammad Razi Akbar', '230', 'TETAP'],
            ['Sudarma', '231', 'TETAP'],
            ['Miswar', '232', 'TETAP'],
            ['Heriadi', '240', 'TETAP'],
            ['Fitriah, SE', '283', 'TETAP'],
            ['Agus Salim', '297', 'TETAP'],
            ['Muhammad Ilham Fauzi, SH', '307', 'TETAP'],
            ['Usman', '337', 'TETAP'],
            ['Ilham', '345', 'TETAP'],
            ['Armia', '353', 'TETAP'],
            ['Fazlon', '360', 'TETAP'],
            ['Maidy, SE', '380', 'TETAP'],
            ['Muliadi', '382', 'TETAP'],
            ['Rozi Mulia, S.Pd', '407', 'TETAP'],
            ['Nanda Maulana', '414', 'TETAP'],
            ['Adnan Muharram Bali', '438', 'TETAP'],
            ['Khairil Anwar, S.Sos', '23', 'KONTRAK'],
            ['M. Fadhil Ilham', '24', 'KONTRAK'],
            ['Rizki M. Febriansyah', '38', 'MAGANG'],
        ],
        'Seksi ADM Bagian Pengolahan Air Limbah' => [
            ['Muhammad Ali, A.Pk', '287', 'TETAP'],
            ['Rinal Febrian, SE', '386', 'TETAP'],
            ['Nazita Mandavia Zamora Hasan', '401', 'TETAP'],
        ],
        'Seksi Teknik Bagian Pengolahan Air Limbah' => [
            ['Andri Kurniawan', '264', 'TETAP'],
            ['Zulfahmi', '301', 'TETAP'],
            ['Rahmad Haspriady, SE', '314', 'TETAP'],
            ['Ahmadi, SE', '315', 'TETAP'],
            ['Muhammad Ulya', '384', 'TETAP'],
            ['Hanif Saputra, SE', '431', 'TETAP'],
            // "Staf Pada Instalasi Pengolahan Air Limbah" di roster — digabung ke sini (bukan
            // Seksi ADM) karena sifatnya teknis, bukan salah satu Seksi resmi Bagian PAL.
            ['Muhammad Dhafir', '25', 'KONTRAK'],
        ],
    ];

    /**
     * Buat semua staf riil untuk sebuah Department. Return jumlah yang dibuat supaya
     * caller tahu kapan harus fallback ke factory random (department tanpa data riil).
     */
    public function makeUsersFor(Department $department): int
    {
        $staff = self::REAL_STAFF[$department->name] ?? [];

        foreach ($staff as [$name, $nik, $status]) {
            // Nik dipakai sebagai suffix username karena roster ini punya nama kembar asli
            // (mis. dua "Zulham", dua "Faisal", dua "Muhammad Aulia") — slug nama saja bisa
            // tabrakan di kolom username yang unique.
            $username = Str::slug($name, '.').'.'.$nik;

            User::factory()->create([
                'department_id' => $department->id,
                'job_level' => 4,
                'name' => $name,
                'nik' => $nik,
                'username' => $username,
                'email' => "{$username}@tirtadaroy.id",
                'instansi' => \in_array($status, ['PRAMAGANG', 'MAGANG', 'KONTRAK'], true) ? 'KOPKARTIRDA' : 'PERUMDAM_TD',
                'employment_status' => $status,
            ]);
        }

        return count($staff);
    }
}
