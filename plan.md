# PRODUCT REQUIREMENT & TECHNICAL DOCUMENT (V3.0)

**Sistem Terpadu HRIS & Penilaian Kinerja (KPI) Perumdam Tirta Daroy**

> V3.0 mendokumentasikan ulang struktur organisasi riil perusahaan dan mengganti logika
> "atasan langsung" yang statis dengan algoritma resolusi penilai yang dihitung dinamis
> dari struktur departemen saat ini. Bagian yang berisi asumsi yang perlu dikonfirmasi
> ditandai ⚠️.
>
> **Update 2026-07-14:** revisi §5 — Kasi kini dinilai **4 penilai** (bukan 3): ditambah
> Direktur Utama, bobot jadi rata 25/25/25/25 (Staf & Kabag-setara tetap 3 penilai,
> 33/33/34). Ditambahkan §14 (spesifikasi layar aplikasi mobile Capacitor + Vue) dan §15
> (kebutuhan REST API mobile — endpoint yang sudah ada vs belum, beserta payload).
>
> **Update 2026-07-14 (lanjutan, §13):** 3 tabel baru — `kpi_evaluator_weights` (master
> bobot penilai, bukan hard-code), `kpi_plan_reviews` (riwayat approve/revisi, bukan kolom
> tunggal); `kpi_plans.status` tambah nilai `SUBMITTED`. Live Location "pejabat lihat
> pejabat" (§14.4) dikonfirmasi **lintas bagian & level se-Perumdam**, bukan dibatasi satu
> direktorat seperti asumsi awal.
>
> **Update 2026-07-15:** semua pertanyaan §13 selesai dikonfirmasi. Ditambahkan §16 —
> fitur **Izin** (Cuti/Sakit/Dinas Luar) menggantikan "Validasi Cuti" lama: `leave_requests`
> diperluas (alasan, dokumen per-type, alur approval beda per jenis — HR untuk Cuti/DL,
> atasan pertama untuk Sakit). Panel admin (`CutiController`/`SakitController`/
> `DinasLuarController` + Livewire table masing-masing) **dibangun di sesi ini**; bagian
> mobile (§16.7–16.8) masih dokumentasi, menyusul Fase D/E.

## 1. Arsitektur Sistem & Tech Stack (Kondisi Riil Terpasang)

- **Backend:** Laravel 13 (PHP 8.5).
- **Web Panel Admin:** Blade konvensional + Alpine.js + TailAdmin (Tailwind template).
- **Mobile Frontend (rencana, belum dibangun):** Capacitor JS + Vue.js.
- **Database:** MySQL. Redis untuk buffer GPS (rencana, belum dipakai).
- **Real-time:** Laravel Reverb sudah terpasang (composer + npm), tapi baru ada 1 channel default (`App.Models.User.{id}`) — belum dipakai untuk live tracking.
- **Auth:** Session (web, `Auth::attempt` + middleware `admin` berbasis `users.is_admin`) dan Sanctum (API, baru endpoint login/logout/user).
- **API Docs:** Dedoc/Scramble — belum diverifikasi terpasang/dipakai.

---

## 2. Status Implementasi Saat Ini

### Sudah dibangun end-to-end

| Fitur                                                                                                                                                                                                                                             | Lokasi                                                                                             |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| Auth admin (login/logout, via email/NIK/username)                                                                                                                                                                                                 | `Web\AuthController`, `pages/auth/signin.blade.php`                                                |
| CRUD Pegawai (kecuali show), dropdown Jabatan→Departemen kaskade dinamis, filter Jabatan/Departemen + pencarian nama/NIK/username/email                                                                                                           | `Web\EmployeeController`, `pages/admin/employees/*`                                                |
| Master Kategori KPI (bobot 5 komponen + kategori pengurang Integritas)                                                                                                                                                                            | `Web\KpiCategoryController`, `pages/admin/kpi-categories/index.blade.php`                          |
| Manajemen Kantor (CRUD lokasi geofence, tipe Radius atau Poligon bebas)                                                                                                                                                                           | `Web\LocationController`, `pages/admin/locations/*` — lihat §8.1.1                                 |
| Buka/ubah status Periode KPI (tanpa efek samping hitung skor)                                                                                                                                                                                     | `Web\KpiPeriodController`, `pages/admin/kpi-periods/index.blade.php`                               |
| Approve/Reject Cuti + auto-inject kehadiran                                                                                                                                                                                                       | `Web\LeaveRequestController` + `AttendanceService::injectAttendanceForApprovedLeave`               |
| Profil self-service (foto, ganti password)                                                                                                                                                                                                        | `Web\ProfileController`                                                                            |
| Dashboard ringkas (hitung pegawai/hadir/telat/cuti)                                                                                                                                                                                               | `Web\DashboardController`                                                                          |
| Kehadiran (tab admin, baca-saja) — daftar absen per tanggal, badge lokasi (dalam/luar geofence, §8.1.1), status Tepat Waktu/Terlambat (masuk ≤08:00) & Tepat Waktu/Cepat (pulang ≥16:30), filter tanggal/status, sort kolom, page-size 5/10/20/50 | `Web\AttendanceController`, `pages/admin/attendances/index.blade.php`, `Location::containsPoint()` |

### Sudah ada schema + model, TAPI belum ada controller/route/view (kosong)

Attendance clock-in mobile (input koordinat saat absen — kolom `clock_in_lat/long`, `clock_out_lat/long` sudah ditambahkan ke tabel, tapi belum ada endpoint yang mengisinya; tab admin Kehadiran di atas baru sisi baca), `kpi_plans`/`kpi_evaluations` (pengajuan target & penilaian), `violation_reports`, `kpi_disputes`, `kpi_final_scores`, `daily_activities`. Tidak ada Service untuk hitung skor KPI atau deteksi apel otomatis.

### API mobile

Hanya `POST /api/login`, `POST /api/logout`, `GET /api/user`. Belum ada endpoint attendance/kpi/tracking/violation untuk aplikasi mobile.

### Utang teknis yang teridentifikasi (dibereskan bertahap, bukan sekarang)

- `Web\SidebarController::getMenuData()` adalah dead code (menu demo TailAdmin) — nav asli dikendalikan `app/Helpers/MenuHelper.php`.
- `resources/views/components/{ui,form,tables}` sebagian besar adalah boilerplate demo TailAdmin yang tidak dipakai.
- `EvaluatorResolutionService` (§5) **belum dibangun** — `department_id` + `job_level` sudah jadi satu-satunya sumber kebenaran struktur organisasi (§4 sudah diterapkan penuh: kolom `direct_supervisor_id`/`final_supervisor_id` sudah dihapus dari `users`), tapi belum ada service yang menghitung Penilai 1–3 on-the-fly. Halaman detail pegawai (read-only Penilai 1–3, §6) juga belum dibangun.

---

## 3. Struktur Organisasi

Organisasi dimodelkan sebagai **satu pohon `departments` self-referencing** (`parent_department_id`), bukan daftar datar. Level pohon persis mengikuti `job_level` pada `users`:

| job_level | Peran                                                            | Contoh                                                                      |
| --------- | ---------------------------------------------------------------- | --------------------------------------------------------------------------- |
| 1         | Direksi                                                          | Direktur Utama, Direktur ADM & Keuangan, Direktur Teknik                    |
| 2         | Kabag / Kepala Cabang / Kepala Unit / Staf Ahli ("kabag-setara") | Kabag Keuangan, Kepala Cabang Teuku Umar, Kanit TI, Staf Ahli Bidang Teknik |
| 3         | Kasi / Kepala Seksi ("kasi-setara")                              | Kasi Anggaran, Kepala Seksi Teknik Cabang Syiah Kuala                       |
| 4         | Staf                                                             | seluruh pegawai pelaksana                                                   |

### 3.1. Pohon Organisasi Lengkap

```
Direktur Utama (job_level 1, root)
├── Direktur ADM & Keuangan  (job_level 1, directorate=KEUANGAN)
│   ├── Bagian Keuangan               [type=BAGIAN]
│   │   ├── Seksi Anggaran            [type=SEKSI]
│   │   ├── Seksi Kas/Gaji            [type=SEKSI]
│   │   └── Seksi Akuntansi           [type=SEKSI]
│   ├── Bagian Umum                   [type=BAGIAN]
│   │   ├── Seksi Kepegawaian dan Hukum
│   │   ├── Seksi Sekretariat dan ADM
│   │   ├── Seksi Gudang
│   │   └── Seksi Perlengkapan
│   ├── Bagian Hubungan Pelanggan     [type=BAGIAN]
│   │   ├── Seksi Pelayanan Pelanggan
│   │   ├── Seksi Pembaca Meter
│   │   └── Seksi Rekening
│   ├── Satuan Pengawas Internal (SPI) [type=SPI]
│   │   ├── Seksi Pengawasan Bidang Umum & Keuangan
│   │   └── Seksi Pengawasan Bidang Teknik
│   ├── Unit Teknologi Informasi      [type=UNIT, tanpa seksi — staf melekat langsung]
│   └── Staf Ahli Bidang Administrasi [type=STAF_AHLI, tanpa bawahan]
│
└── Direktur Teknik  (job_level 1, directorate=TEKNIK)
    ├── Bagian Perencanaan Teknik dan Pengawasan Teknik [type=BAGIAN]
    │   ├── Seksi Perencanaan Teknik
    │   └── Seksi Pengawasan Teknik
    ├── Bagian Produksi                                  [type=BAGIAN]
    │   ├── Seksi Operasi
    │   ├── Seksi Laboratorium
    │   └── Seksi Pemeliharaan
    ├── Bagian Transmisi dan Distribusi                  [type=BAGIAN]
    │   ├── Seksi Sistem Pendistribusian Air
    │   └── Seksi Penanganan Kebocoran & Pengendalian Kehilangan Air
    ├── Bagian PAL (Pengolahan Air Limbah)                [type=PAL]
    │   ├── Seksi ADM Bagian Pengolahan Air Limbah
    │   └── Seksi Teknik Bagian Pengolahan Air Limbah
    ├── Cabang Sultan Iskandar Muda                       [type=CABANG]
    │   ├── Seksi Adm Cabang Sultan Iskandar Muda
    │   └── Seksi Teknik Cabang Sultan Iskandar Muda
    ├── Cabang Teuku Nyak Arief                           [type=CABANG]
    │   ├── Seksi Adm Cabang Teuku Nyak Arief
    │   └── Seksi Teknik Cabang Teuku Nyak Arief
    ├── Cabang Syiah Kuala                                [type=CABANG]
    │   ├── Seksi Adm Cabang Syiah Kuala
    │   └── Seksi Teknik Cabang Syiah Kuala
    ├── Cabang Teuku Umar                                 [type=CABANG]
    │   ├── Seksi Adm Cabang Teuku Umar
    │   └── Seksi Teknik Cabang Teuku Umar
    └── Staf Ahli Bidang Teknik [type=STAF_AHLI, tanpa bawahan]
```

### 3.2. Pemetaan Direktorat (menentukan siapa "Direktur Bidang")

- **Direktur ADM & Keuangan** membawahi: Bagian Umum, Bagian Keuangan, Bagian Hubungan Pelanggan, SPI, Unit TI, Staf Ahli Bidang Administrasi.
- **Direktur Teknik** membawahi: sisanya — Bagian Perencanaan Teknik & Pengawasan Teknik, Bagian Produksi, Bagian Transmisi & Distribusi, Bagian PAL, keempat Cabang, Staf Ahli Bidang Teknik.
- `directorate` (KEUANGAN/TEKNIK) disimpan hanya pada node job*level 2 (Bagian/Cabang/Unit/SPI/PAL/StafAhli); node Seksi mewarisi nilai ini dari parent-nya satu query ke atas. Node Direksi tidak punya `directorate` (mereka \_adalah* direktoratnya).

### 3.3. Tipe Departemen & Konsekuensinya

| type                     | Punya seksi anak?                        | Staf (job_level 4) melekat ke node ini langsung? |
| ------------------------ | ---------------------------------------- | ------------------------------------------------ |
| DIREKSI                  | ya (Bagian/Cabang/Unit/SPI/PAL/StafAhli) | tidak                                            |
| BAGIAN, CABANG, SPI, PAL | ya (Seksi)                               | tidak                                            |
| UNIT                     | tidak                                    | **ya** (kasus solo, tidak ada Kasi)              |
| STAF_AHLI                | tidak                                    | tidak (tidak ada bawahan sama sekali)            |
| SEKSI                    | tidak (leaf)                             | ya (kasus normal)                                |

---

## 4. Prinsip Kunci: Tidak Ada "Atasan Langsung" yang Statis

Kolom `users.direct_supervisor_id` dan `final_supervisor_id` (skema lama) **dihapus**. Alasan: siapa atasan/penilai seseorang berubah setiap kali struktur berubah (Kasi kosong, jumlah staf berubah, mutasi) — menyimpannya sebagai FK statis membuat data basi begitu ada perubahan yang tidak disertai migrasi data manual.

Sumber kebenaran cukup dua kolom pada `users`: **`department_id`** (posisi di pohon organisasi) dan **`job_level`**. Siapa atasan/penilai seseorang **dihitung on-the-fly** oleh `EvaluatorResolutionService` (baru, lihat §5) setiap kali dibutuhkan (saat membuka form evaluasi, saat approval absen telat, dsb), bukan dibaca dari kolom yang di-freeze saat user dibuat.

---

## 5. Logika Penilai (Evaluator) KPI

Skema 5-bucket total 100 tidak berubah (§7). Komponen **Kinerja Teknis (50%)** dinilai oleh **3 penilai untuk Staf dan Kabag-setara**, tapi **4 penilai khusus untuk Kasi** (ditambah Direktur Utama — lihat §5.2). Bobot per slot **tidak lagi seragam**: 33/33/34 untuk Staf & Kabag-setara, 25/25/25/25 untuk Kasi (§5.4).

### 5.1. Prinsip Umum

- Penilai 1 = atasan langsung satu tingkat di atas (Kasi untuk Staf, Kabag untuk Kasi, Direktur Bidang untuk Kabag-setara).
- Penilai 2 = atasan tetap di posisi "normal"-nya (Kabag untuk Staf, Direktur Bidang untuk Kasi, Direktur Utama untuk Kabag-setara) — **posisi ini tidak ikut naik** walaupun Penilai 1 sudah diisi orang yang sama karena fallback (mis. Kasi kosong). Tidak ada logika anti-dobel di sini: kalau fallback membuat satu orang mengisi lebih dari satu slot, itu **diperbolehkan** — bobot tiap slot tetap dihitung terpisah, jadi orang itu otomatis mendapat porsi lebih besar.
- **Khusus Kasi**: ada slot tambahan, Penilai 3 = Direktur Utama (skip-level, seragam untuk semua Kasi apa pun direktoratnya) — jadi baik Direktur Bidang maupun Direktur Utama sama-sama menilai Kasi, persis seperti keduanya sudah sama-sama menilai Kabag-setara. Slot ini tidak berlaku untuk Staf.
- Penilai terakhir tiap level (Penilai 3 untuk Staf/Kabag-setara, Penilai 4 untuk Kasi) = rekan sejawat, dengan fallback berjenjang kalau rekan tidak ada (lihat per-level di §5.2) dan aturan anti-silang saat rekan tersedia (§5.3).

Hanya Staf yang punya kemungkinan atasan langsung kosong (Kasi vakan). Kasi dan Kabag-setara **selalu** punya atasan lengkap (setiap Bagian pasti punya Kabag, setiap Direktorat pasti punya Direktur) — jadi Penilai 1/2 di dua level itu tidak butuh fallback.

### 5.2. Rincian per Level

**Staf (job_level 4)** — posisi di sebuah Seksi (atau langsung di Unit TI):

- Penilai 1 = Kasi seksinya. Jika kosong → Kabag/Kacab/Kanit bagian induk.
- Penilai 2 = Kabag/Kacab/Kanit bagian induk — tetap, walau Penilai 1 sudah diisi orang yang sama karena Kasi kosong (rangkap slot diperbolehkan, lihat §5.1).
- Penilai 3 = 1 rekan seksi (§5.3). Tidak ada rekan → diambil alih Kasi (jika ada). Kasi juga kosong → diambil alih Kabag.
- **Kasus ekstrem**: Kasi kosong **dan** tidak ada rekan seksi → Kabag mengisi ketiga slot sekaligus, penilaian 100% oleh Kabag.

**Kasi / Kepala Seksi (job_level 3) — 4 penilai:**

- Penilai 1 = Kabag/Kacab bagian induknya.
- Penilai 2 = Direktur Bidang (Keuangan/Teknik, sesuai §3.2).
- Penilai 3 = Direktur Utama (**baru**, §5.1 — skip-level, sama untuk semua Kasi).
- Penilai 4 = 1 rekan Kasi lain dalam bagian yang sama (untuk Cabang, otomatis selalu pasangan ADM↔Teknik).

**Kabag / Kepala Cabang / Kepala Unit / Staf Ahli (job_level 2):**

- Penilai 1 = Direktur Bidang (Keuangan untuk Umum/Keuangan/Hublang/SPI/IT/Staf Ahli ADM; Teknik untuk sisanya).
- Penilai 2 = Direktur Utama (skip-level, seragam untuk semua).
- Penilai 3 = 1 rekan job_level 2 lain **dalam direktorat yang sama**, dibahas bersama tiap bulan oleh Direktur Bidang tsb. Khusus **Staf Ahli** (tidak punya rekan setingkat langsung): rekan diacak dari seluruh pegawai job_level 2 (Kabag/Kacab/Kanit) — bukan dibatasi satu direktorat, sesuai pengecualian yang sudah ada.

**Direksi (job_level 1):** di luar cakupan KPI bulanan ini (belum ada aturan dari user — dianggap out-of-scope untuk versi ini).

### 5.3. Aturan Rekan Sejawat (Penilai 3) — Anti-Silang

Larangan: dalam satu grup, A menilai B **dan** B menilai A pada periode yang sama tidak boleh terjadi kecuali grup hanya berisi 2 orang (tidak terhindarkan, memang diizinkan).

- Grup = 1 orang (sendirian): tidak ada Penilai 3 dari rekan — diambil alih atasan (lihat per-level di atas).
- Grup = 2 orang: keduanya saling menilai (mutual), sesuai instruksi eksplisit.
- Grup ≥ 3 orang: acak urutan, lalu rangkai sebagai siklus (A→B, B→C, C→A, ...) sehingga setiap orang menilai tepat satu rekan dan dinilai oleh rekan yang berbeda — tidak butuh tabel riwayat pasangan tambahan, cukup dihitung ulang tiap periode.

### 5.4. Bobot antar Penilai (dari total 50% Kinerja)

Tidak seragam lagi — Kasi punya 4 slot, Staf & Kabag-setara tetap 3 (§5.1, update 2026-07-14).

| Level        | Penilai 1             | Penilai 2             | Penilai 3                | Penilai 4               |
| ------------ | --------------------- | --------------------- | ------------------------ | ----------------------- |
| Staf         | Kasi — 33%            | Kabag — 33%           | Rekan seksi — 34%        | —                       |
| Kasi         | Kabag — 25%           | Direktur Bidang — 25% | Direktur Utama — 25%     | Rekan sesama Kasi — 25% |
| Kabag-setara | Direktur Bidang — 33% | Direktur Utama — 33%  | Rekan sesama Kabag — 34% | —                       |

**(2026-07-14, dikonfirmasi)** Bobot di atas disimpan di master table `kpi_evaluator_weights` (job_level, slot, weight), bukan hard-code — HRD bisa ubah tanpa deploy kode. Lihat §10 untuk skema, §13 untuk riwayat keputusan.

---

## 6. Form Tambah/Edit Pegawai — Deteksi Dinamis, Bukan Hard-Code

Field dasar: **nama, NIK (2–3 digit), username, email, password, instansi, employment_status, jabatan**. `jabatan` bukan kolom tersendiri — cuma pilihan di form yang menentukan `job_level` + `department_id` disimpan. 5 pilihan `jabatan`, memetakan 1:1 ke `job_level` (Direktur Utama dan Direktur Bidang sama-sama job_level 1, dibedakan oleh node departemen yang dipilih):

- **Jabatan = Direktur Utama (job_level 1, root)** → tidak ada dropdown departemen, langsung terpasang ke satu-satunya node root Direksi (cuma ada 1 orang di seluruh Perumdam).
- **Jabatan = Direktur Bidang (job_level 1)** → dropdown "Direktorat mana" menampilkan node job_level 1 non-root yang belum punya kepala aktif (Direktur ADM & Keuangan / Direktur Teknik).
- **Jabatan = Kepala Bagian/Kepala Cabang/Kepala Unit (job_level 2, termasuk Staf Ahli)** → dropdown "Departemen mana" menampilkan seluruh node job_level 2 yang belum punya kepala aktif (Bagian/Cabang/Unit/SPI/PAL/Staf Ahli). `department_id` = node itu sendiri.
- **Jabatan = Kepala Seksi (job_level 3)** → dropdown "Seksi mana" (hanya node type=SEKSI). Field "Bagian" **read-only, auto-terisi** dari `seksi.parent`.
- **Jabatan = Staf (job_level 4)** → dropdown "Seksi mana" (atau langsung "Unit TI" untuk kasus solo). Field "Bagian" auto-terisi dari parent seksi (dua level ke atas jika seksi cabang).

Form **tidak** meminta memilih "atasan langsung" secara manual — itu dihitung dari `department_id` + `job_level` saat dibutuhkan (§4). Halaman detail pegawai cukup menampilkan hasil hitungan (read-only): siapa Penilai 1–3 pegawai ini saat ini, berapa rekan seksinya, apakah Kasi-nya kosong — semua dari query, bukan dari kolom tersimpan.

### 6.1. Instansi & Status Kepegawaian

- `instansi`: **PERUMDAM_TD** (Tirta Daroy, instansi utama) atau **KOPKARTIRDA** (koperasi karyawan). "Koperasi" cukup diwakili `instansi = KOPKARTIRDA` — bukan nilai `employment_status` terpisah.
- `employment_status`: **TETAP, PEGAWAI_80, PRAMAGANG, MAGANG, KONTRAK**. `PEGAWAI_80` = status transisi dari pegawai Koperasi (`instansi` lama `KOPKARTIRDA`) menjadi pegawai Tirta Daroy (`instansi` baru `PERUMDAM_TD`), digaji 80% selama masa transisi sebelum resmi `TETAP`.

### 6.2. Mutasi Pegawai — Departemen yang Sudah Ada Kepalanya

Untuk job_level 1–3, dropdown Departemen di form **tetap menampilkan dan tetap bisa memilih** node yang sudah punya kepala aktif — opsinya **tidak dikunci**, hanya diberi label siapa pemegangnya sekarang (mis. "Bagian Umum — sudah dijabat: Budi") plus teks peringatan di bawah field. Sengaja tidak dikunci sama sekali (berubah dari desain awal V3.0): mengunci jabatan yang sudah terisi bikin admin tidak bisa menambah staf baru atau memutasi/swap kapan pun perlu — validasi occupied hanya informatif, keputusan tetap di tangan admin.

Alur mutasi: assign langsung ke departemen yang mau dituju meski masih ada pemegangnya — tidak ada auto-vacate otomatis dan tidak ada operasi "swap satu klik", jadi kalau ingin slot lama benar-benar kosong, admin tetap perlu edit pemegang lama secara terpisah. Staf (job_level 4) tidak pernah terkunci karena satu Seksi/Unit memang boleh diisi banyak staf.

Tidak ada validasi server-side yang menolak submit karena departemen sudah terisi (`EmployeeRequest` cuma memastikan `department_id` valid) — label `occupied_by` di UI adalah satu-satunya lapis, murni peringatan.

---

## 7. Penilaian Akhir Bulanan (5 Bucket, Total 100)

1. **Kinerja Teknis (50%)** — lihat §5 untuk rincian 3 penilai.
2. **Kehadiran (20%)** — rasio hari hadir, dihitung otomatis dari sistem absensi (HRIS).
3. **Apel Pagi (5%)** — khusus hari Senin (apel mingguan), bukan setiap hari kerja. `is_apel = true` otomatis kalau ada absen tercatat pada hari Senin itu. Rasio 5% = jumlah Senin ber-`is_apel=true` dibagi total Senin dalam periode.
4. **Pakaian Dinas (5%)** — default 100, dipotong oleh aduan foto tervalidasi.
5. **Integritas (20%)** — default 100 per pegawai, dipecah ke 8 sub-kategori berbobot (§7.1), dikurangi oleh aduan atasan tervalidasi pada kategori terkait (skema pengurang, bukan skema tambah).

### 7.1. Sub-Kategori Integritas (total 100, sebelum dikonversi ke bobot 20%)

| Skor Maks | Kategori                        |
| --------- | ------------------------------- |
| 10        | Etika                           |
| 10        | Kerjasama Tim                   |
| 20        | Jujur dan Transparansi          |
| 15        | Loyalitas                       |
| 10        | Kepatuhan dan Budaya Organisasi |
| 15        | Ketepatan Waktu                 |
| 10        | Inisiatif                       |
| 10        | Tugas Tambahan                  |

Pegawai mulai dari skor penuh di tiap kategori (total 100). Aduan atasan tervalidasi pada kategori tertentu mengurangi skor kategori itu (floor 0, tidak minus). Skor akhir = total 8 kategori setelah pengurangan, dikonversi proporsional ke bobot 20% bucket. **(2026-07-14, dikonfirmasi)** Pengurangan **flat per kejadian** sesuai `kpi_integrity_categories.deduction_value` (§10) — bukan berjenjang. Titik terbuka yang tersisa: dedup harian (§8.4) dicek saat submit aduan (tolak dobel) atau saat hitung skor (abaikan dobel secara diam-diam) — lihat §13.

---

## 8. Alur Kerja HRIS Lainnya (Tidak Berubah dari Rencana Sebelumnya)

### 8.1. Absensi Geofencing

Absen ≤ 08:00 → dianggap tepat waktu. Khusus hari Senin, absen tepat waktu tsb otomatis menandai `is_apel = true` (§7 poin 3) — hari lain tidak relevan untuk apel. Di luar toleransi → wajib isi alasan, status `PENDING` sampai disetujui atasan (dihitung via §5, bukan FK statis).

#### 8.1.1. Manajemen Kantor (Master Lokasi Geofence)

`locations` bukan daftar tertutup (bukan enum "Pusat/Cabang/WTP") — admin bebas menamai dan menambah lokasi kapan saja, termasuk lokasi non-kantor yang dipakai sesekali (mis. "Balai Kota (Apel Gabungan)" saat ada apel pagi gabungan lintas instansi).

Tiap lokasi punya `type`, dua pilihan:

- **RADIUS** — titik pusat (`lat`, `long`) + `radius_meters`. Cocok untuk kantor dengan area kurang lebih melingkar/sederhana.
- **POLYGON** — titik pusat tetap disimpan (buat label/pusat peta), plus `polygon` (JSON array `{lat, lng}[]`, minimal 3 titik) yang membentuk batas area bebas bentuk. Dipakai kalau area kantor tidak beraturan (mis. kompleks WTP yang bentuknya tidak melingkar) dan radius tunggal kurang akurat.

Form pegawai admin (`pages/admin/locations/form.blade.php`) pakai peta mini **Leaflet** (sudah terpasang di `package.json`, tidak perlu dependency baru) — klik peta untuk menandai titik pusat (mode RADIUS, marker bisa di-drag) atau menambah titik satu-satu membentuk poligon (mode POLYGON, ada tombol "Hapus titik terakhir"/"Reset"). Koordinat dikirim ke server lewat hidden input JSON, divalidasi & di-decode di `LocationRequest::prepareForValidation()`.

`Location::containsPoint(lat, lng)` (Haversine untuk RADIUS, ray-casting untuk POLYGON) sudah dipakai tab admin **Kehadiran** (`Web\AttendanceController`, `pages/admin/attendances/index.blade.php`) untuk menandai badge hijau/merah per baris absen — cocok dengan salah satu `locations` (hijau, nama lokasi ditampilkan) atau tidak (merah, "Luar Lokasi Kantor"). **Belum terhubung** ke alur clock-in mobile — belum ada endpoint yang mengisi `attendances.clock_in_lat/long`/`clock_out_lat/long` (Fase B/D).

`database/seeders/AttendanceSeeder.php` mengisi data dummy 30 hari terakhir (Senin-Sabtu, Minggu diloncat) untuk seluruh pegawai yang sudah ada — jam kerja Senin-Jumat 08:00-16:30, Sabtu 08:00-12:00, dengan variasi tepat waktu/terlambat/pulang cepat dan koordinat tersebar di 3 lokasi kantor riil yang sudah digambar admin (Kantor Pusat, Kantor Cabang Selatan, WTP Siron) plus sebagian sengaja di luar geofence (badge merah). Dipanggil dari `DatabaseSeeder`, idempotent (skip tanggal yang sudah ada datanya).

### 8.2. Cuti (Hybrid)

Ajukan di HP → proses kertas manual → HRD klik Approve di web → `AttendanceService::injectAttendanceForApprovedLeave` (sudah ada) mengisi kehadiran 100% pada rentang tanggal.

### 8.3. Live Tracking

Ping lokasi ke `user_current_locations` (1-to-1, overwrite) tiap 1 menit. `last_updated_at` > 3 menit → indikator offline. GPS mati otomatis di luar jam kerja (>17:00).

### 8.4. Aduan 360° (Tiered Evidence)

Pakaian Dinas: terbuka untuk semua pelapor, wajib foto. Integritas: terkunci hanya untuk atasan terhadap bawahan (dihitung via §5), wajib deskripsi + pilih salah satu dari 8 kategori (§7.1). Validasi oleh atasan langsung terlapor. Daily cap: aduan berulang kategori sama di hari sama untuk orang sama hanya dihitung 1×.

### 8.5. Logbook

Input bebas (teks + foto opsional), dibatasi H-2 mundur. Tidak ada alur approval — Kasi/Kabag memantau proaktif dari profil bawahan.

---

## 9. Edge Case & Mitigasi (Diperbarui — Sekarang Ditangani Algoritma §5, Bukan Kode Khusus)

| Edge Case                                            | Ditangani oleh                                                                                                                                              |
| ---------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Kasi kosong                                          | §5.2 — Penilai 1 turun ke Kabag, Penilai 2 tetap Kabag (rangkap slot, tanpa eskalasi)                                                                       |
| Kasi kosong + staf sendirian di seksi (kombinasi)    | §5.2 kasus ekstrem — Kabag mengisi Penilai 1, 2, dan 3 sekaligus (100% oleh Kabag)                                                                          |
| Unit TI solo (tanpa Kasi, staf melekat ke node UNIT) | §5.2; sama seperti "Kasi kosong" tanpa kode terpisah                                                                                                        |
| Staf Ahli (tanpa bawahan sama sekali)                | Dinilai langsung Direktur Bidang; rekan sejawat diambil dari pool Kabag/Kacab/Kanit (§5.2)                                                                  |
| Staf sendirian di seksi (0 rekan, Kasi ada)          | §5.2/§5.3 — Penilai 3 diambil alih Kasi                                                                                                                     |
| Cabang (selalu 2 Kasi: ADM & Teknik)                 | §5.3 — grup 2 orang → mutual otomatis                                                                                                                       |
| Spam aduan                                           | Daily capping, §8.4                                                                                                                                         |
| Baterai GPS                                          | Tracking mati otomatis di luar jam kerja                                                                                                                    |
| Sanggahan KPI                                        | Dibuka tanggal 1–3, khusus Kasi & Kabag-setara (Staf tidak bisa dispute) — keputusan final ada di evaluator yang skornya disanggah, via `kpi_evaluation_id` |

---

## 10. Skema Database (Perubahan vs. Migrasi Saat Ini)

```text
Table departments {
  id int [pk, increment]
  name varchar
  type varchar [note: "ENUM: DIREKSI, BAGIAN, CABANG, UNIT, SPI, PAL, STAF_AHLI, SEKSI"]
  parent_department_id int [ref: > departments.id, null]
  directorate varchar [null, note: "ENUM: KEUANGAN, TEKNIK — hanya diisi di node job_level 2, node SEKSI mewarisi dari parent"]
}

Table users {
  id int [pk, increment]
  nik varchar [unique]
  name varchar
  username varchar [unique]
  email varchar [unique]
  password varchar
  department_id int [ref: > departments.id]
  job_level tinyint [note: "1=Direksi, 2=Kabag/Kacab/Kanit/StafAhli, 3=Kasi, 4=Staf"]
  instansi varchar [note: "ENUM: PERUMDAM_TD, KOPKARTIRDA"]
  employment_status varchar [note: "ENUM: TETAP, PEGAWAI_80, PRAMAGANG, MAGANG, KONTRAK — lihat §6.1"]
  leave_balance int [default: 12]
  is_admin boolean [default: false]
  photo_path varchar [null]
  // direct_supervisor_id & final_supervisor_id DIHAPUS — lihat §4
}

Table locations {
  id int [pk, increment]
  name varchar [note: "bebas, admin yang menamai — bukan enum tertutup, lihat §8.1.1"]
  type varchar [default: "RADIUS", note: "ENUM: RADIUS, POLYGON"]
  lat decimal(10,8)
  long decimal(11,8)
  radius_meters int [null, note: "wajib kalau type=RADIUS"]
  polygon json [null, note: "array {lat,lng}[], min 3 titik, wajib kalau type=POLYGON"]
}

Table kpi_evaluations {
  id int [pk, increment]
  kpi_plan_id int [ref: > kpi_plans.id]
  evaluator_id int [ref: > users.id]
  evaluator_role varchar [note: "ENUM: PENILAI_1, PENILAI_2, PENILAI_3, PENILAI_4 (ganti dari KASI/KABAG/REKAN/KANIT_SOLO — lebih generik krn siapa pengisi tiap tier berbeda per job_level; PENILAI_4 baru dipakai utk slot Direktur Utama pada Kasi, §5)"]
  score int
}

Table kpi_plans {
  ...
  status varchar [note: "ENUM: DRAFT, SUBMITTED, APPROVED, REJECTED — SUBMITTED baru ditambah (2026-07-14), state 'menunggu approval' terpisah dari DRAFT, §13 poin 4"]
  self_assessment_photo_path varchar [null, note: "BARU (2026-07-14) — evidence foto self-assessment fase Working (§14.5), kolom tunggal, pola sama dgn photo_path di users/daily_activities/violation_reports (§10)"]
}

Table kpi_evaluator_weights {
  id int [pk, increment]
  job_level tinyint [note: "3=Kasi, 2=Kabag-setara — Staf (4) juga bisa didata di sini walau saat ini sama dgn Kabag-setara (33/33/34), utk konsistensi satu sumber kebenaran"]
  slot varchar [note: "ENUM: PENILAI_1, PENILAI_2, PENILAI_3, PENILAI_4 (PENILAI_4 hanya terisi utk job_level 3/Kasi)"]
  weight tinyint [note: "dari 50% Kinerja — sum per job_level harus 100 (lalu dikonversi ke bobot 50%), §5.4"]

  // BARU (2026-07-14, §13 poin 1) — master table, pola sama dgn kpi_component_weights (§10).
  // Dibuat karena bobot ini sudah terbukti berubah sekali dalam sesi ini (3→4 penilai Kasi).
}

Table kpi_plan_reviews {
  id int [pk, increment]
  period_id int [ref: > kpi_periods.id]
  user_id int [ref: > users.id, note: "pemilik rencana kinerja yang direview"]
  reviewer_id int [ref: > users.id, note: "atasan pertama, §5.1 Penilai 1"]
  action varchar [note: "ENUM: APPROVED, REVISION_REQUESTED"]
  comment text [null]
  created_at datetime

  // BARU (2026-07-14, §13 poin 5) — riwayat tiap putaran approve/revisi rencana kinerja
  // (§14.5 sub-tab 4.3), bukan kolom tunggal — mendukung berkali-kali putaran revisi.
}

Table leave_requests {
  id int [pk, increment]
  user_id int [ref: > users.id]
  type varchar [note: "ENUM: CUTI, SAKIT, DINAS_LUAR"]
  start_date date
  end_date date
  reason text [null, note: "BARU (2026-07-15) — alasan cuti / kegiatan DL, kolom ini sebelumnya tidak ada sama sekali"]
  attachment_path varchar [null, note: "sudah ada — dipakai ulang multi-guna per type: Cuti = scan surat acc Direktur (diisi HR saat approve), Sakit = surat dokter (wajib jika >1 hari), DL = SPPD/surat pengantar"]
  status varchar [note: "BARU (2026-07-15) — ganti nama dari hr_final_status, ENUM PENDING/APPROVED/REJECTED tidak berubah. Nama lama menyesatkan karena approver Sakit bukan HR, §16"]
  rejection_reason text [null, note: "BARU (2026-07-15) — wajib diisi saat status=REJECTED (HR utk Cuti, atasan pertama utk Sakit)"]
  approved_by_id int [ref: > users.id, null, note: "BARU (2026-07-15) — siapa yang approve/reject: staf HR (Cuti/DL) atau atasan pertama (Sakit)"]
  source varchar [default: "HR_MANUAL", note: "BARU (2026-07-15) — ENUM: APP, HR_MANUAL. APP = diajukan pegawai lewat mobile; HR_MANUAL = HR input langsung dari kertas fisik"]
  dl_batch_uuid varchar [null, index, note: "BARU (2026-07-15) — grup beberapa baris DL (multi-pegawai) yang berbagi 1 SPPD/surat pengantar & tanggal yang sama, §16.4"]
}
```

`attendances` mendapat tambahan 4 kolom nullable untuk tab Kehadiran (§2, §8.1.1): `clock_in_lat decimal(10,8)`, `clock_in_long decimal(11,8)`, `clock_out_lat decimal(10,8)`, `clock_out_long decimal(11,8)` — diisi nanti oleh endpoint clock-in mobile (Fase D), untuk saat ini dipakai `Location::containsPoint()` menghitung badge dalam/luar lokasi kantor kalau datanya ada.

Tabel lain (`user_current_locations`, `kpi_periods`, `violation_reports`, `kpi_disputes`, `kpi_final_scores`, `daily_activities`) tetap seperti migrasi saat ini — tidak ada perubahan. `kpi_plans` berubah (tambah nilai status `SUBMITTED`, lihat blok skema di atas). `leave_requests` berubah signifikan (§16) — lihat blok skema di atas. **(2026-07-14, §13 poin 4)** `committees`/`committee_members` **dihapus total** — migration, model (`Committee`, `CommitteeMember`), dan tabelnya di database sudah di-drop; tujuannya tidak pernah dijelaskan dan tidak dipakai fitur mana pun. Kalau nanti dibutuhkan lagi, rancang ulang dari kebutuhan bisnis yang jelas, bukan dipakai lagi dari skema lama.

Selain itu ada 2 tabel baru di luar skema V3.0 awal, untuk fitur Master Kategori KPI (§2): `kpi_component_weights` (5 baris tetap — Kinerja/Kehadiran/Apel/Pakaian Dinas/Integritas, tiap baris `component` + `weight`, sum harus 100) dan `kpi_integrity_categories` (master data bebas tambah/hapus — `name` + `deduction_value`, seed awal 8 kategori §7.1). Ditambah 2 tabel baru lagi hasil keputusan §13 (2026-07-14): `kpi_evaluator_weights` dan `kpi_plan_reviews` (lihat blok skema di atas).

---

## 11. Struktur Folder (Kondisi Riil + Tambahan yang Direncanakan)

```text
app/
├── Http/Controllers/
│   ├── Web/          (ada: Attendance, Auth, Dashboard, Employee, KpiCategory, KpiPeriod,
│   │                  LeaveRequest, Location, Profile)
│   │                 (belum ada, perlu dibuat: Department,
│   │                  KpiPlan, KpiEvaluation, ViolationReport, KpiDispute,
│   │                  KpiFinalScore, DailyActivity)
│   └── Api/           (ada: Auth. Belum ada: Attendance, KpiPlan, Tracking, Violation)
├── Services/
│   ├── AttendanceService.php        (ada — injeksi cuti)
│   ├── EvaluatorResolutionService.php (BARU — implementasi algoritma §5)
│   └── KpiEvaluationService.php      (BARU — hitung skor 5-bucket §7)
│   (validasi geofence radius/poligon dipakai langsung via `Location::containsPoint()`,
│    dipakai tab admin Kehadiran (§8.1.1); belum ada endpoint clock-in mobile yang memicunya)
├── Models/  (semua 15 model sudah ada, relasi User perlu disesuaikan §4:
│             hapus directSupervisor()/finalSupervisor()/subordinates(),
│             tambah accessor read-only via EvaluatorResolutionService)
```

---

## 12. Roadmap (Diperbarui Sesuai Progres Riil)

### Fase A — Refactor Fondasi Organisasi (prioritas berikutnya, sebelum lanjut fitur baru)

1. ✅ Migrasi: tambah `parent_department_id`, `directorate` ke `departments`.
2. ✅ Migrasi: tambah `username` (unique) dan hapus `direct_supervisor_id`, `final_supervisor_id` dari `users`.
3. ✅ Tulis ulang `DatabaseSeeder` mengikuti pohon §3.1 secara lengkap: 3 Direksi, 15 Kabag-setara, 29 Kasi (semua terisi, tanpa Kasi kosong di seeder utama), 72 Staf dengan jumlah per Seksi bervariasi (cycle 1/2/3/4) + 1 solo di Unit TI.
4. ✅ Update `EmployeeController` + form pegawai mengikuti §6: dropdown Jabatan (job_level) → Departemen dikaskade via Alpine, departemen yang sudah punya kepala aktif (job_level 1–3) ditampilkan tapi disabled + nama pemegangnya (§6.2), ditegakkan juga di server (`EmployeeRequest`). Field `username` ditambahkan, `employment_status` diupdate ke 5 nilai (§6.1), login `AuthController` menerima email/NIK/username.
5. ✅ Update `employees/index.blade.php` — kolom "Atasan Langsung" (statis) dihapus, kolom "Jabatan" ditambah, plus filter (Jabatan/Departemen) & pencarian (nama/NIK/username/email).
6. ⬜ Bangun `EvaluatorResolutionService` (§5) + test Pest untuk tiap edge case (Kasi kosong, Kasi kosong + sendirian (Kabag rangkap 3 slot), Unit TI solo, Staf Ahli, grup 2 orang, grup ≥3 orang, sendirian). **Belum dikerjakan** — halaman detail pegawai (read-only Penilai 1–3) juga menunggu ini.

### Fase B — Lengkapi Halaman Admin yang Masih Kosong

`Department`, `KpiPlan`/`KpiEvaluation` (form penilaian mengikuti §5), `ViolationReport`, `KpiDispute`, `KpiFinalScore`, `DailyActivity` — masing-masing controller + route + view. ✅ `Location` (Manajemen Kantor) sudah selesai — lihat §2, §8.1.1. ✅ `Attendance` — tab **Kehadiran** (baca-saja: daftar absen + badge lokasi + status tepat waktu/telat/pulang cepat, §2, §8.1.1) sudah selesai; approval telat (`late_reason`/`supervisor_approval`) dan endpoint clock-in yang mengisi koordinat masih menyusul (Fase D).

### Fase C — `KpiEvaluationService`

Hitung `kpi_final_scores` dari 5 bucket (§7) saat periode ditutup (`KpiPeriodController::updateStatus` transisi ke `CLOSED` harus memicu kalkulasi — saat ini belum ada efek samping apa pun).

### Fase D — REST API Mobile

Lengkapi `routes/api.php`: attendance clock-in/out (geofencing), kpi-plan submit, violation report upload, tracking ping → Reverb broadcast. Rincian endpoint per fitur (existing vs belum, payload) — lihat §15.

### Fase E — Mobile App (Capacitor + Vue)

Sesuai rencana awal — belum dimulai. Spesifikasi layar — lihat §14.

---

## 13. Pertanyaan Terbuka untuk Konfirmasi (⚠️ ringkasan semua asumsi di atas)

Sudah dikonfirmasi user: bobot 33/33/34 untuk Staf & Kabag-setara (§5.4); Penilai 2 tidak eskalasi saat Kasi kosong, tetap Kabag walau rangkap slot (§5.1–5.2); Apel Pagi (5%) hanya dihitung dari hari Senin (§7 poin 3); Integritas (20%) dipecah 8 sub-kategori skema pengurang (§7.1); "Koperasi" cukup diwakili `instansi = KOPKARTIRDA`, bukan `employment_status` terpisah; `PEGAWAI_80` = status transisi Koperasi → Tirta Daroy dengan gaji 80% (§6.1); `username` field terpisah dari `email`, login menerima email/NIK/username (§6, §10) — sudah diimplementasikan. **(2026-07-14)** Kasi kini 4 penilai (Kabag, Direktur Bidang, Direktur Utama, rekan), bobot rata 25/25/25/25, bukan lagi 3 penilai seragam semua level (§5 revisi). **(2026-07-14)** Besaran pengurangan skor per aduan integritas tervalidasi = **flat per kejadian** sesuai `kpi_integrity_categories.deduction_value` (§10), bukan berjenjang. **(2026-07-14)** Bobot per-slot penilai (§5.4) disimpan di master table `kpi_evaluator_weights`, bukan hard-code (§10). **(2026-07-14)** Status "menunggu approval" pada `kpi_plans` = nilai enum baru `SUBMITTED`, bukan kolom timestamp terpisah (§10). **(2026-07-14)** Komentar approval/revisi (§14.5 sub-tab 4.3) disimpan di tabel riwayat `kpi_plan_reviews` (1 baris per putaran approve/revisi), bukan kolom tunggal (§10). **(2026-07-14)** Live Location "pejabat lihat sesama pejabat" (§14.4) = **seluruh pejabat se-Perumdam**, lintas bagian dan lintas level (job*level 1–3 saling lihat), **bukan** dibatasi satu direktorat seperti asumsi awal — plus switch ke mode "bawahan sendiri saja". **(2026-07-14, ronde 2)** Fase Working/self-assessment (§14.5) **otomatis** ikut penutupan `kpi_periods.status` DRAFT→EVALUATION oleh HRD — tidak ada tombol "ajukan" atau state terpisah per-pegawai; `self_assessment*\*`cukup`PUT`bebas selama periode masih DRAFT dan plan sudah`APPROVED`(§15.4). **(2026-07-14, ronde 2)**`committees`/`committee_members`**dihapus total** dari codebase (migration, model, tabel di DB) — lihat §10. **(2026-07-14, ronde 2)** Dedup harian aduan (§8.4) = **diterima semua, diabaikan diam-diam saat`KpiEvaluationService`menghitung skor** — bukan ditolak saat submit. **(2026-07-14, ronde 2)** Auth mobile = token Sanctum per device, tanpa refresh-token, pola sama dengan`POST /api/login` yang sudah ada. **(2026-07-15, dikonfirmasi eksplisit)** Direktur Utama **tidak pernah approve** rencana kinerja siapa pun, hanya menilai (4.4) — Kasi di-approve Kabag, Kabag di-approve Direktur Bidang, Dirut cuma dapat tab **4.4 Beri Penilaian**, tab **4.3 Approval** tidak muncul untuknya sama sekali.

Semua poin pertanyaan terbuka §13 sudah selesai dikonfirmasi. Poin serupa untuk fitur **Izin** (Cuti/Sakit/Dinas Luar) — lihat §16.6.

---

## 14. Aplikasi Mobile (Capacitor + Vue) — Spesifikasi Layar

Auth via Sanctum token (sama pola dengan §6/§10 web: login menerima email/NIK/username). Semua layar di bawah butuh token, kecuali Login.

### 14.1. Login

Form: identifier (email/NIK/username) + password — reuse validasi yang sama dengan `Web\AuthController`. Sukses → simpan token, redirect Halaman Utama.

### 14.2. Halaman Utama (Home)

- Sapaan dinamis: "Selamat pagi/siang/sore, {nama}" (berdasar jam device).
- 1 baris peringatan fase KPI periode berjalan (Draft / Draft Approval / Working / Evaluation / Dispute), sesuai posisi user saat ini di alur §14.5.
- Skor KPI bulan lalu, ditampilkan **hanya kalau** `kpi_final_scores` periode -1 sudah ada untuk user itu.
- Tanggal & jam (device clock).
- Minimap kecil menampilkan posisi user saat ini (reuse Leaflet, sudah ada di `package.json`, §8.1.1).
- Tombol **Absen Masuk** / **Absen Keluar** — nonaktif otomatis kalau sudah absen hari itu untuk arah yang sama.
- Tombol **Refresh Lokasi** — ping GPS manual, terpisah dari auto-ping §8.3 (tiap 1 menit).

### 14.3. Riwayat Absen (1 Bulan)

List per tanggal: jam masuk/keluar, status Tepat Waktu/Terlambat/Cepat (`Attendance::statusMasuk()`/`statusPulang()`), badge lokasi dalam/luar geofence (`Location::containsPoint()`/`matchedLocation()` — logic sama dengan tab admin Kehadiran, §8.1.1). Filter bulan, default bulan berjalan.

### 14.4. Live Location

- **Staf**: peta hanya menampilkan rekan **satu seksi/departemen yang sama** (`department_id` sama, `job_level = 4`). Tidak bisa melihat pejabat sama sekali.
- **Pejabat (job_level 1–3 — Kasi, Kabag-setara, Direksi)**: default melihat **seluruh pejabat lain se-Perumdam**, lintas bagian **dan** lintas level — **(dikonfirmasi user 2026-07-14)** Kasi Anggaran bisa lihat Kabag Produksi walau beda bagian dan beda level; pejabat di bawah Direktur Keuangan bisa lihat pejabat di bawah Direktur Teknik. Tidak dibatasi direktorat/level seperti awalnya diasumsikan. Plus **tombol switch** untuk beralih melihat hanya bawahan sendiri (staf dalam satu bagian yang dipimpinnya). Butuh service visibility baru (bukan `EvaluatorResolutionService` — beda scope: siapa boleh **lihat** siapa, bukan siapa **menilai** siapa), lihat §15.7 "Kebutuhan Backend Tambahan".
- Sumber data: `user_current_locations` (1 baris per user, overwrite — §8.3), **bukan riwayat**. Tidak perlu tabel log baru, cukup titik terakhir.
- Update dari client: tiap 1 menit **atau** tiap pergerakan ≥10 meter, mana yang lebih dulu tercapai (throttle di client, kirim ping saat salah satu syarat terpenuhi). GPS berhenti otomatis di luar jam kerja (>17:00, §8.3).
- Real-time: idealnya broadcast Reverb per scope (channel private per departemen/level) begitu ping masuk, supaya client tidak polling — belum ada (§1: baru channel default `App.Models.User.{id}`).

### 14.5. KPI Bulanan

Navigasi sub-tab dari bawah (bottom sheet/segmented, bukan banyak tombol berjejer). Bar fase di atas: **Draft → Draft Approval → Working → Evaluation (status Penilai 1/2/3/4 sudah menilai atau belum) → Dispute → Final**.

| Sub-tab                                 | Isi                                                                                                                                                                                                                                                                                                                                                                | Siapa lihat                                                                                                  |
| --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------ |
| 4.1 KPI Bulanan                         | Isi rencana kinerja: target + indikator sukses + bobot (akumulasi ≤ 50, dikunci HRD saat masa pembuatan ditutup). Submit untuk approval.                                                                                                                                                                                                                           | Semua job_level 2–4 (Staf, Kasi, Kabag-setara — §5.2). Direksi **tidak** (out-of-scope KPI bulanan, §5.2).   |
| 4.2 KPI Tahunan                         | Sama pola dengan 4.1, tapi **menyusul** — cukup placeholder UI ("segera hadir"), tanpa backend dulu.                                                                                                                                                                                                                                                               | idem, non-Direksi                                                                                            |
| 4.3 Approval Rencana Kinerja            | List nama yang perlu di-approve/direvisi — **khusus atasan pertama** (Staf→Kasi, Kasi→Kabag, Kabag→Direktur Bidang, §5.1 Penilai 1). Klik nama → detail per item rencana + kolom komentar + tombol Approve/Revisi.                                                                                                                                                 | Kasi, Kabag-setara, Direktur Bidang. **Tidak** untuk Dirut (dia bukan atasan pertama siapa pun, §13 poin 2). |
| 4.4 Beri Penilaian                      | List nama yang perlu dinilai user ini (dihasilkan dari `EvaluatorResolutionService`, §5), diklik untuk masuk form skor per target rencana kinerja. Kasi: menilai seluruh staf bawahannya + sesama Kasi terpilih (§5.3). Kabag: menilai seluruh bawahan (staf+Kasi) + sesama Kabag terpilih. Direktur Bidang & Dirut: menilai Kabag & Kasi (keduanya, §5.2 revisi). | Semua level yang berperan sebagai penilai di §5, termasuk Dirut & Direktur Bidang.                           |
| 4.5 Aduan Disiplin Pakaian & Integritas | Cari nama (dropdown searchable), foto (wajib utk Pakaian Dinas), deskripsi, pilih jenis pelanggaran (kategori Integritas dari `kpi_integrity_categories` kalau kategori = Integritas). Integritas terkunci hanya untuk atasan→bawahan (§8.4); Pakaian Dinas terbuka semua pelapor.                                                                                 | Semua, sesuai aturan §8.4.                                                                                   |

**Direksi**: tab KPI hanya menampilkan 4.4 (Direktur Bidang & Dirut menilai Kabag+Kasi) dan 4.3 khusus Direktur Bidang saja (approve rencana Kabag di direktoratnya). 4.1/4.2 disembunyikan total.

### 14.6. Profil

Foto, nama, email, username. Skor KPI bulan lalu (`kpi_final_scores`). **Logbook**: list per tanggal (input dibatasi H-2 mundur, §8.5), deskripsi + foto opsional, checkbox "terkait KPI" → kalau dicentang, pilih salah satu target dari `kpi_plans` milik user sendiri. Tombol Logout.

---

## 15. Kebutuhan REST API Mobile

Base: `routes/api.php`, semua route (kecuali login) di belakang `auth:sanctum`. Status **Sudah ada** hanya 3 endpoint (§2); sisanya di bawah **belum ada** — perlu dibuat di Fase D.

### 15.1. Auth & Home

| Endpoint           | Status                         | Diterima (request)                                                         | Dikembalikan (response)                                                                                                                                                                                                 | Catatan                                                          |
| ------------------ | ------------------------------ | -------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| `POST /api/login`  | ✅ Sudah ada                   | `{ login, password }` (`login` = email/NIK/username, sama pola §6/§10 web) | `{ token, user }`                                                                                                                                                                                                       | `Api\AuthController`                                             |
| `POST /api/logout` | ✅ Sudah ada                   | —                                                                          | `{ message }`                                                                                                                                                                                                           |                                                                  |
| `GET /api/user`    | ✅ Sudah ada (perlu diperkaya) | —                                                                          | Saat ini raw `$request->user()`. Perlu tambah `department`, `jabatan_label`, `photo_url` (reuse `User::jabatanLabel()`/`photoUrl()`, §6/§2 model User sudah punya method-nya) supaya Home & Profil tidak perlu 2x call. |                                                                  |
| `GET /api/home`    | ⬜ Belum ada                   | —                                                                          | `{ greeting_name, kpi_phase: {period, status, my_step}, last_month_score, attendance_today: {clocked_in, clocked_out} }`                                                                                                | Gabungan ringkas utk §14.2, hindari N call terpisah dari mobile. |

### 15.2. Absensi (§14.2, §14.3)

| Endpoint                                   | Status       | Diterima                                       | Dikembalikan                                                                                                                                                                                                                                                                          | Catatan                                                                             |
| ------------------------------------------ | ------------ | ---------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| `POST /api/attendance/clock-in`            | ⬜ Belum ada | `{ lat, long }`                                | Row `attendances` baru: `status` via `Attendance::statusMasuk()`, `is_apel` otomatis kalau Senin & tepat waktu (§7 poin 3), `matched_location` (nama lokasi atau null = luar geofence). Kalau telat → wajib field tambahan `late_reason` di request, `supervisor_approval = PENDING`. | Kolom `clock_in_lat/long` sudah ada di schema (§10), tinggal endpoint yang mengisi. |
| `POST /api/attendance/clock-out`           | ⬜ Belum ada | `{ lat, long }`                                | Update row hari ini: `clock_out`, `clock_out_lat/long`, status pulang (`statusPulang()`).                                                                                                                                                                                             | Tolak kalau belum ada clock-in hari itu.                                            |
| `GET /api/attendance/history?month=&year=` | ⬜ Belum ada | query `month`, `year` (default bulan berjalan) | List absen sebulan + badge lokasi (`matchedLocation()`).                                                                                                                                                                                                                              | §14.3                                                                               |

### 15.3. Live Location (§14.4, §8.3)

| Endpoint                                                 | Status       | Diterima                                                                                 | Dikembalikan                                                                                                                                                                                                                                                                                                                                                                              | Catatan                                                                                                                                                                        |
| -------------------------------------------------------- | ------------ | ---------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `POST /api/location/ping`                                | ⬜ Belum ada | `{ lat, long }`                                                                          | `{ ok: true }`                                                                                                                                                                                                                                                                                                                                                                            | Upsert `user_current_locations` (overwrite, bukan insert baru). Sebaiknya server juga menolak/no-op di luar jam kerja (>17:00) — jangan hanya percaya client berhenti sendiri. |
| `GET /api/location/colleagues?scope=peers\|subordinates` | ⬜ Belum ada | query `scope` (opsional, default `peers`; hanya pejabat yang boleh kirim `subordinates`) | List `{ user_id, name, lat, long, last_updated_at, is_online }` (`is_online` = `last_updated_at` < 3 menit, §8.3) sesuai visibility §14.4. `scope=peers`: Staf → 1 departemen yang sama; Pejabat (job_level 1–3) → **seluruh pejabat se-Perumdam**, lintas bagian & level (dikonfirmasi 2026-07-14). `scope=subordinates` (khusus pejabat): staf dalam satu bagian yang dipimpinnya saja. | Butuh service visibility baru — lihat §15.7.                                                                                                                                   |

### 15.4. KPI — Rencana Kinerja, Approval, Penilaian, Dispute (§14.5)

| Endpoint                                  | Status                                                                    | Diterima                                                                  | Dikembalikan                                                                                                                                              | Catatan                                                                                                                                                                                                                   |
| ----------------------------------------- | ------------------------------------------------------------------------- | ------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /api/kpi/periods/current`            | ⬜ Belum ada                                                              | —                                                                         | `{ id, month, year, status }` (`kpi_periods.status`: DRAFT/EVALUATION/DISPUTE/CLOSED)                                                                     |                                                                                                                                                                                                                           |
| `GET /api/kpi/plans?period_id=`           | ⬜ Belum ada                                                              | query `period_id`                                                         | List `kpi_plans` milik user                                                                                                                               |                                                                                                                                                                                                                           |
| `POST /api/kpi/plans`                     | ⬜ Belum ada                                                              | `{ period_id, items: [{ target_description, weight }] }`                  | List plan tersimpan, status DRAFT                                                                                                                         | Validasi total `weight` ≤ 50 per user/period.                                                                                                                                                                             |
| `PUT /api/kpi/plans/{id}`                 | ⬜ Belum ada                                                              | `{ target_description, weight }`                                          | Plan terupdate                                                                                                                                            | Hanya kalau status masih DRAFT.                                                                                                                                                                                           |
| `POST /api/kpi/plans/submit`              | ⬜ Belum ada                                                              | `{ period_id }`                                                           | Semua plan user → status `SUBMITTED` (§10, §13)                                                                                                           |                                                                                                                                                                                                                           |
| `GET /api/kpi/approvals`                  | ⬜ Belum ada                                                              | —                                                                         | List nama bawahan langsung dengan plan SUBMITTED (khusus atasan pertama, §5.1)                                                                            | Bergantung `EvaluatorResolutionService`/hierarki (§2, §11).                                                                                                                                                               |
| `PUT /api/kpi/plans/{id}/self-assessment` | ⬜ Belum ada — **baru ditemukan saat review §13**, belum ada di draf awal | `{ self_assessment_score, self_assessment_note, self_assessment_photo? }` | Plan terupdate                                                                                                                                            | Fase **Working** (§14.5): isi setelah plan `APPROVED`, sebelum evaluator mulai menilai. Kolom `self_assessment_photo_path` baru (§10). Mekanisme "diajukan buat dinilai" masih §13 poin terbuka — lihat catatan di bawah. |
| `GET /api/kpi/approvals/{userId}`         | ⬜ Belum ada                                                              | —                                                                         | Detail plan user tsb (semua item + bobot)                                                                                                                 |                                                                                                                                                                                                                           |
| `POST /api/kpi/approvals/{userId}`        | ⬜ Belum ada                                                              | `{ action: APPROVED\|REVISION_REQUESTED, comment }`                       | Insert baris baru ke `kpi_plan_reviews` (§10); kalau APPROVED → semua plan user tsb terkunci jadi `APPROVED`; kalau REVISION_REQUESTED → balik ke `DRAFT` | Riwayat semua putaran approve/revisi tersimpan di `kpi_plan_reviews`, bukan ditimpa (§13).                                                                                                                                |
| `GET /api/kpi/evaluations/pending`        | ⬜ Belum ada                                                              | —                                                                         | List `{ user_id, name, evaluator_role }` yang perlu dinilai user ini                                                                                      | `evaluator_role` = PENILAI_1..4 hasil resolusi §5.                                                                                                                                                                        |
| `GET /api/kpi/evaluations/{userId}`       | ⬜ Belum ada                                                              | —                                                                         | Detail plan + self-assessment user yang mau dinilai                                                                                                       |                                                                                                                                                                                                                           |
| `POST /api/kpi/evaluations/{userId}`      | ⬜ Belum ada                                                              | `{ scores: [{ kpi_plan_id, score }] }`                                    | Insert `kpi_evaluations` dengan `evaluator_role` sesuai slot penilai user saat ini                                                                        | Anonim untuk Penilai 3 (rekan) — response ke pihak dinilai **tidak** boleh expose `evaluator_id` per skor (§ tugas user: "penilai 3 anonim").                                                                             |
| `GET /api/kpi/disputes/eligible`          | ⬜ Belum ada                                                              | —                                                                         | `{ can_dispute: bool, window_open_until }`                                                                                                                | Khusus Kasi/Kabag, window tanggal 1–3 (§9).                                                                                                                                                                               |
| `POST /api/kpi/disputes`                  | ⬜ Belum ada                                                              | `{ kpi_evaluation_id, reason, evidence_file }`                            | `kpi_disputes` baru, status PENDING                                                                                                                       |                                                                                                                                                                                                                           |
| `GET /api/kpi/final-score?period_id=`     | ⬜ Belum ada                                                              | query `period_id`                                                         | `kpi_final_scores` breakdown 5 bucket + grand total, atau `null` kalau belum dihitung (Fase C)                                                            |                                                                                                                                                                                                                           |

### 15.5. Aduan (§14.5 sub-tab 4.5, §8.4)

| Endpoint                   | Status       | Diterima                                                                                                                                                                   | Dikembalikan                                                             | Catatan                                                                                                                                                                                                                                                            |
| -------------------------- | ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `GET /api/users/search?q=` | ⬜ Belum ada | query `q`                                                                                                                                                                  | List ringkas `{ id, name, nik, department }` utk autocomplete form aduan | Versi ringkas utk semua pegawai (bukan admin-only seperti pencarian di `EmployeeController`).                                                                                                                                                                      |
| `POST /api/violations`     | ⬜ Belum ada | `{ reported_user_id, category: PAKAIAN_DINAS\|INTEGRITAS, description, integrity_category_id? (wajib jika INTEGRITAS), photo? (wajib jika PAKAIAN_DINAS), incident_date }` | `violation_reports` baru, status PENDING                                 | Integritas hanya boleh dari atasan langsung (dihitung §5) ke bawahan — server harus validasi ini, bukan cuma UI. Daily-cap (kategori sama + orang sama + hari sama = dihitung 1×) sudah didesain di §8.4, perlu diimplementasi di validasi/`KpiEvaluationService`. |

### 15.6. Logbook & Profil (§14.6)

| Endpoint                        | Status       | Diterima                                                                                               | Dikembalikan                       | Catatan                                                                      |
| ------------------------------- | ------------ | ------------------------------------------------------------------------------------------------------ | ---------------------------------- | ---------------------------------------------------------------------------- |
| `GET /api/logbook?month=&year=` | ⬜ Belum ada | query `month`, `year`                                                                                  | List `daily_activities` milik user |                                                                              |
| `POST /api/logbook`             | ⬜ Belum ada | `{ activity_date (maks H-2), description, photo?, kpi_plan_id? (hanya jika "terkait KPI" dicentang) }` | Activity tersimpan                 |                                                                              |
| `POST /api/profile/photo`       | ⬜ Belum ada | `multipart/form-data: photo`                                                                           | `{ photo_url }`                    | Mirror `Web\ProfileController::update` (sudah ada utk web, §2) versi mobile. |

### 15.7. Kebutuhan Backend Tambahan (di luar endpoint, prasyarat Fase D)

1. `EvaluatorResolutionService` (§5, §11) — **belum dibangun**, jadi prasyarat keras untuk approvals, evaluations, dan (parsial) live-location visibility. Skema baca bobot dari `kpi_evaluator_weights` (poin 7), bukan hard-code.
2. Migrasi: tambah nilai `SUBMITTED` ke `kpi_plans.status` (§10, §13).
3. Migrasi: `kpi_evaluations.evaluator_role` tambah nilai `PENILAI_4` + migration lama (comment masih `KASI/KABAG/REKAN/KANIT_SOLO`) diselaraskan ke `PENILAI_1..4` (§10).
4. Migrasi: tabel baru `kpi_plan_reviews` (§10) — riwayat approve/revisi, dipakai `POST /api/kpi/approvals/{userId}` (§15.4).
5. `LocationVisibilityService` (baru, terpisah dari `EvaluatorResolutionService`) — Staf: 1 seksi/departemen sama; Pejabat (job_level 1–3): seluruh pejabat se-Perumdam lintas bagian & level, plus mode switch ke bawahan sendiri (§14.4, dikonfirmasi 2026-07-14).
6. Channel Reverb privat untuk broadcast live location — baru ada default `App.Models.User.{id}` (§1); tanpa ini, live location harus polling. Karena scope "pejabat" sekarang company-wide (bukan per-direktorat), channel broadcast-nya juga perlu 1 channel besar per role (mis. `private-pejabat-locations`) bukan per departemen.
7. Migrasi: tabel baru `kpi_evaluator_weights` (§10, §13) — dipakai `EvaluatorResolutionService` (poin 1), seed awal sesuai §5.4 (33/33/34 utk Staf & Kabag-setara, 25/25/25/25 utk Kasi).

---

## 16. Fitur Izin (Cuti, Sakit, Dinas Luar) — Panel Admin + Rencana Mobile

**(2026-07-15)** Mengganti/memperluas fitur "Validasi Cuti" (§2) yang lama — sebelumnya cuma 1 tabel generik tanpa alasan, tanpa dokumen, tanpa alur approval berjenjang. Panel admin bagian ini **dibangun sekarang** (lihat §16.6 — status implementasi). Bagian mobile (§16.7–16.8) masih **dokumentasi saja**, menyusul Fase D/E seperti fitur mobile lain di dokumen ini.

### 16.1. Prinsip Umum

Tiga jenis izin, workflow beda-beda, tapi tetap 1 tabel `leave_requests` (§10) dibedakan `type` — pola yang sama dipakai `violation_reports` (dibedakan `category`).

| Jenis          | Siapa yang approve                                   | Sumber pengajuan                | Dokumen wajib                                                                       |
| -------------- | ---------------------------------------------------- | ------------------------------- | ----------------------------------------------------------------------------------- |
| **Cuti**       | HR (di panel admin)                                  | App **atau** HR manual (kertas) | Scan surat sudah di-acc Direktur (diupload HR saat approve, atau saat input manual) |
| **Sakit**      | Atasan pertama (di app, bukan HR)                    | App saja                        | Surat dokter, **hanya wajib kalau > 1 hari**                                        |
| **Dinas Luar** | Tidak ada approval — SPPD fisik sudah jadi otorisasi | HR manual saja (bukan pegawai)  | SPPD / surat pengantar (wajib)                                                      |

### 16.2. Cuti — Alur Hybrid Online/Offline

Cuti selalu berakhir dengan **kertas fisik bertanda tangan** (staf → Kasi → Kabag → Direktur), app cuma mempercepat/mendokumentasikan prosesnya:

1. **Via app**: pegawai isi form (tanggal mulai, tanggal selesai, alasan) → tekan "Ajukan" → tersimpan `status=PENDING`, `source=APP`, belum ada dokumen. Proses kertas tetap jalan di luar sistem seperti biasa (staf teken, naik ke Kabag, ke Direktur, balik ke HR).
2. HR buka panel admin, lihat baris PENDING ini. Kalau kertasnya sudah di-acc Direktur: tekan **Approve** → muncul form konfirmasi tanggal + **upload dokumen** (scan surat acc) → submit → `status=APPROVED`, `attachment_path` terisi, `approved_by_id` = HR yang approve, otomatis panggil `AttendanceService::injectAttendanceForApprovedLeave` (sudah ada, §8.2) supaya pegawai itu tidak bisa absen di rentang tanggal itu.
3. Kalau ternyata **ditolak Direktur di kertas**: HR tekan **Tolak**, wajib isi `rejection_reason` (kolom teks, tidak boleh kosong) — pegawai lihat alasan penolakan di riwayat app-nya.
4. **Kalau tidak pernah lewat app** (murni kertas dari awal sampai akhir): HR langsung **input manual** di panel admin — pilih pegawai, tanggal, alasan, upload dokumen, `source=HR_MANUAL`, langsung `status=APPROVED` (HR baru input setelah proses kertas selesai). Baris ini otomatis muncul juga di riwayat cuti pegawai tsb di app (query `leave_requests where user_id=X`, tidak peduli `source`).

### 16.3. Sakit — Approval oleh Atasan, Bukan HR

- **1 hari** (mendadak): di Profil app, tombol "Ajukan Sakit" → pilih tanggal **hari ini atau besok saja** (tidak bisa pilih tanggal lain) → tidak perlu upload apa-apa → `status=PENDING`, `source=APP`.
- **>1 hari**: tombol terpisah untuk rentang tanggal → **wajib upload surat dokter** (`attachment_path`) → `status=PENDING`.
- **Approval**: bukan HR — atasan pertama pegawai (hierarki sama dengan §5.1 Penilai 1: Staf→Kasi, fallback Kabag kalau Kasi kosong; Kasi→Kabag; Kabag→Direktur Bidang; Direksi di luar cakupan, sama seperti KPI §5.2). Atasan lihat notifikasi/list di app-nya sendiri, klik nama → lihat alasan + (kalau >1 hari) foto surat dokter → tekan Approve/Tolak.
- **Panel admin HR**: **read-only** — HR cuma lihat daftar semua pengajuan sakit + statusnya (siapa yang approve/tolak), tidak ada tombol aksi (§16.6).
- Approved → sama seperti Cuti, panggil `injectAttendanceForApprovedLeave` (attendance jadi `SAKIT`).

### 16.4. Dinas Luar — Input HR, Bisa Banyak Pegawai Sekaligus

- **Bukan** fitur pegawai — hanya HR yang input di panel admin, karena dasarnya adalah SPPD/surat pengantar fisik yang sudah ada duluan (tidak perlu approval lagi di sistem, SPPD itu sendiri sudah otorisasi).
- Form: nama kegiatan (`reason`), tanggal mulai–selesai, upload 1 file SPPD/surat pengantar (`attachment_path`) — dan **pilih pegawai bisa lebih dari satu** (multi-select dari data pegawai) kalau satu SPPD sama meng-cover banyak orang dengan tanggal identik.
- Submit → server membuat 1 baris `leave_requests` per pegawai terpilih, semuanya berbagi `dl_batch_uuid` yang sama (ULID baru per submit) + `attachment_path` yang sama, langsung `status=APPROVED`, `source=HR_MANUAL`, lalu `injectAttendanceForApprovedLeave` per baris (attendance jadi `DINAS_LUAR`).
- Kalau sebagian pegawai punya tanggal beda (walau kegiatan/SPPD dasarnya sama) → **submit form terpisah** per kelompok tanggal (`dl_batch_uuid` beda per submit) — bukan 1 form yang mengizinkan tanggal berbeda per baris, supaya form tetap sederhana.

### 16.5. Skema Database

Lihat blok skema `leave_requests` di §10 — semua kolom baru dijelaskan di sana (`reason`, `status` ganti nama dari `hr_final_status`, `rejection_reason`, `approved_by_id`, `source`, `dl_batch_uuid`). Tidak ada tabel baru — cukup 1 migration tambahan ke `leave_requests` yang sudah ada, ikut konvensi migration additive (§4, §13) di proyek ini.

### 16.6. Panel Admin (Web) — Status Implementasi

Mengikuti pola nav existing: 1 grup sidebar "Izin" dengan 3 sub-halaman (persis seperti grup "KPI" → 2 halaman, "Absensi" → 2 halaman, §11), masing-masing controller+Livewire table sendiri (pola `LocationsTable`/`JabatanTable`), bukan tab dalam 1 halaman — konsisten dengan seluruh panel admin yang sudah ada.

| Sub-halaman    | Kemampuan                                                                                       | Controller            | Livewire         |
| -------------- | ----------------------------------------------------------------------------------------------- | --------------------- | ---------------- |
| **Cuti**       | List + filter + **Input manual** (HR) + **Approve** (upload dokumen) + **Tolak** (wajib alasan) | `CutiController`      | `CutiTable`      |
| **Sakit**      | List + filter — **read-only**, tidak ada aksi                                                   | `SakitController`     | `SakitTable`     |
| **Dinas Luar** | List + filter + **Input** (multi-pegawai, 1 SPPD) — tidak ada approve/tolak                     | `DinasLuarController` | `DinasLuarTable` |

`LeaveRequestController`/`LeaveRequestsTable`/nav "Validasi Cuti" yang lama **digantikan** oleh susunan di atas (dihapus, bukan dipertahankan paralel — supaya tidak ada 2 pintu masuk data yang sama).

### 16.7. Aplikasi Mobile — Spesifikasi Layar (rencana, belum dibangun — lihat §14 untuk pola serupa)

Masuk sebagai tab besar baru **"Izin"** (setara tab Absen/Live Location/KPI/Profil di §14), sub-tab bawah:

- **Cuti**: form ajukan (tanggal mulai–selesai, alasan, tombol Ajukan) + riwayat cuti sendiri (status PENDING/APPROVED/REJECTED, alasan tolak kalau ada).
- **Sakit — Ajukan** (khusus pegawai): tombol "Sakit 1 Hari" (pilih hari ini/besok, tanpa upload) dan tombol terpisah "Sakit >1 Hari" (rentang tanggal + wajib upload surat dokter).
- **Sakit — Approval** (khusus atasan, muncul hanya kalau user punya bawahan menurut §5.1): list nama bawahan yang punya pengajuan sakit PENDING → klik → lihat alasan + foto surat dokter (kalau ada) → tombol Approve/Tolak.
- **Dinas Luar**: **read-only** — riwayat DL yang HR sudah assign ke pegawai ini, tidak ada form input (§16.4, input cuma di web HR).

### 16.8. Kebutuhan REST API Mobile — Izin

Belum ada satupun di `routes/api.php` (§2) — semua baris di bawah **belum ada**, prasyarat Fase D seperti §15.

| Endpoint                                      | Diterima                                                                                | Dikembalikan                                                                             | Catatan                                                                                                                 |
| --------------------------------------------- | --------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `GET /api/leave?type=CUTI\|SAKIT\|DINAS_LUAR` | query `type` opsional                                                                   | Riwayat izin milik user (semua type kalau tanpa filter)                                  | Satu endpoint gabungan, cukup filter `type` — hindari 3 endpoint list terpisah.                                         |
| `POST /api/leave/cuti`                        | `{ start_date, end_date, reason }`                                                      | Row baru `status=PENDING`, `source=APP`                                                  | §16.2 poin 1.                                                                                                           |
| `POST /api/leave/sick`                        | `{ date }` **atau** `{ start_date, end_date, photo }` (photo wajib kalau range >1 hari) | Row baru `status=PENDING`, `source=APP`                                                  | Mode 1-hari: `date` harus hari ini/besok (validasi server). Mode range: tanpa batas hari ini/besok, tapi `photo` wajib. |
| `GET /api/leave/sick/pending`                 | —                                                                                       | List bawahan langsung (§5.1 Penilai 1) dengan sakit `status=PENDING`                     | Kosong/disembunyikan di UI kalau user tidak punya bawahan (Staf, atau Direksi — di luar cakupan §16.3).                 |
| `POST /api/leave/sick/{id}/approve`           | —                                                                                       | `status=APPROVED`, `approved_by_id`=user ini, trigger `injectAttendanceForApprovedLeave` | Hanya boleh dieksekusi kalau caller memang atasan pertama pemilik request (validasi server via hierarki §5.1).          |
| `POST /api/leave/sick/{id}/reject`            | `{ rejection_reason }`                                                                  | `status=REJECTED`                                                                        | idem otorisasi.                                                                                                         |

**Tidak ada** endpoint submit untuk Dinas Luar dari sisi pegawai — sesuai §16.4, DL cuma dientri HR di web.

---
