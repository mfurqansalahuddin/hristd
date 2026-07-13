# PRODUCT REQUIREMENT & TECHNICAL DOCUMENT (V2.0 - BLADE EDITION)
**Sistem Terpadu HRIS & Penilaian Kinerja (KPI) Perumdam Tirta Daroy**

## 1. Arsitektur Sistem & Tech Stack
* **API & Backend Core:** Laravel 13.x (PHP 8.2+).
* **Web Panel Admin:** Laravel Blade konvensional dipadukan dengan Alpine.js dan UI Library (Flux / TailAdmin) untuk *dashboard* HRD.
* **Mobile Frontend:** Capacitor JS + Vue.js (Akses native GPS & Kamera).
* **Database & Cache:** MySQL / PostgreSQL, Redis (sebagai *buffer* GPS).
* **Real-time Engine:** Laravel Reverb (WebSocket).
* **API Documentation:** Dedoc/Scramble (Otomatisasi dokumentasi *endpoint*).

## 2. Alur Kerja Utama (Core Workflows)
Logika bisnis tidak berubah dari versi sebelumnya, mencakup 4 pilar utama:
1.  **HRIS (Geofencing & Apel):** Absen masuk $\le$ 08:00 otomatis mencatat `is_apel = true`. Telat wajib isi alasan via *pop-up*. Cuti menggunakan alur *Hybrid* (pengajuan via app $\rightarrow$ acc kertas $\rightarrow$ eksekusi final oleh HRD di web).
2.  **Penilaian Akhir (Skema 5 Buckets - Total 100):**
    * Kinerja Teknis (50%): Dinilai Kasi (33%), Kabag (33%), Rekan (34%).
    * Kehadiran (20%): Tarik rasio hari hadir dari HRIS.
    * Apel Pagi (5%): Tarik rasio kehadiran apel dari HRIS.
    * Pakaian Dinas (5%): *Default* 100, potong poin jika ada aduan tervalidasi.
    * Integritas (20%): *Default* 100, potong poin jika ada aduan atasan tervalidasi.
3.  **Sistem Aduan Tervalidasi (Tiered Evidence):** Kategori "Pakaian" wajib foto (terbuka untuk semua). Kategori "Integritas" wajib deskripsi (terkunci hanya untuk atasan). Berlaku penalti maksimal per hari (*Daily Cap*).
4.  **Logbook & Tracking:** HP *ping* lokasi tiap 1 menit. Staf dapat mengisi riwayat aktifitas harian (batas mundur H-2) sebagai bukti kinerja saat akhir bulan.

---

## 3. Alur Kerja Utama (Core Workflows)

### 3.1. HRIS & Absensi Geofencing
1.  **Absen Normal & Apel:** Pegawai klik "Absen Masuk" di area yang diizinkan (WTP/Kantor). Jika timestamp $\le$ 08:00 WIB, sistem otomatis mencatat `is_apel = true`.
2.  **Keterlambatan / Pulang Cepat:** Klik absen di luar jam batas toleransi memunculkan *pop-up* wajib isi alasan. Data di-pending hingga disetujui (ACC) oleh Atasan Langsung.
3.  **Cuti (Alur Hybrid):** Staf klik "Ajukan Cuti" di HP $\rightarrow$ Status: *Menunggu Berkas HR*. Staf memproses tanda tangan kertas manual. Setelah kertas disetujui, HRD klik *Approve* di Web. Sistem otomatis menyuntikkan kehadiran 100% pada rentang tanggal tersebut. DL diinput murni oleh HRD.
4.  **Live Tracking (Tanpa Riwayat):** HP mengirim koordinat ke Redis setiap 1 menit atau saat ada perpindahan signifikan. Data menimpa (*overwrite*) data lama (relasi 1-to-1). Jika *ping* terakhir $>$ 3 menit, peta memunculkan indikator kuning (Offline).

### 3.2. Penilaian KPI Bulanan (Pembagian: 50 - 20 - 5 - 5 - 20)
Total Nilai Akhir adalah 100, dibagi menjadi 5 komponen mutlak:
1.  **Kinerja (Bobot 50%):** Staf mengajukan target. Di akhir bulan dievaluasi oleh: Kasi (33%), Kabag (33%), Rekan Sejawat (34%).
2.  **Kehadiran (Bobot 20%):** Ditarik otomatis dari rasio hari hadir (HRIS).
3.  **Apel Pagi (Bobot 5%):** Ditarik otomatis dari akumulasi harian `is_apel = true`.
4.  **Pakaian Dinas (Bobot 5%):** Bersifat *Default 100*. Hanya berkurang jika ada aduan foto tervalidasi.
5.  **Integritas & Etika (Bobot 20%):** Bersifat *Default 100*. Hanya berkurang jika ada aduan (deskripsi/bukti digital) tervalidasi dari atasan.

### 3.3. Sistem Aduan 360-Derajat (Tiered Evidence)
Sistem untuk memotong skor perilaku (Pakaian Dinas & Integritas) tanpa menginput nilai manual di akhir bulan:
1.  **Kasat Mata (Memotong porsi Pakaian Dinas):** Terbuka untuk dilaporkan oleh *semua pegawai*. Wajib foto dari kamera langsung.
2.  **Abstrak (Memotong porsi Integritas):** Dikunci *hanya untuk Atasan* terhadap bawahannya (mencegah fitnah antar staf). Wajib mengisi deskripsi kronologi atau melampirkan screenshot.
3.  **Validasi:** Nilai hanya terpotong jika Atasan Langsung dari terlapor menekan "Valid" pada laporan yang masuk.

### 3.4. Logbook (Aktifitas Harian)
Fitur *evidence tracking* pasif:
* **Input:** Pegawai mencatat kegiatan harian (Teks + Foto Opsional). Bisa ditautkan ke Rencana Kerja bulanan.
* **Validasi Mundur:** Sistem menolak pengisian lebih dari 2 hari ke belakang (H-2) untuk mencegah rekayasa *diary* di akhir bulan.
* **Akses Pantau:** Tidak ada notifikasi persetujuan ke atasan. Kasi/Kabag memantau secara proaktif melalui profil bawahan saat akan memberikan evaluasi.

---

## 4. Edge Cases & Mitigasi Logika

| Edge Case (Kasus Khusus) | Logika Sistem / Mitigasi |
| :--- | :--- |
| **Unit TI / Staf Sendirian** | Jika jumlah rekan seksi = 0, sistem membatalkan bobot 34% sejawat, dan memberikan 100% hak evaluasi Kinerja kepada Kanit TI. |
| **Kasi Kosong (Definitif)** | Bobot Kasi (33%) ditarik oleh Kabag. Kabag memegang porsi evaluasi 66%. |
| **Staf Ahli (Tanpa Bawahan)** | Penilai langsung adalah Direktur Bidang (100%). Evaluasi rekan sejawat diacak dari pegawai dengan *job level* Kabag/Kacab. |
| **Spam Pelaporan Aduan** | Menerapkan *Daily Capping*. Jika ada 5 aduan Pakaian Dinas untuk orang yang sama di hari yang sama, penalti hanya dihitung 1 kali. |
| **Kelelahan Baterai (GPS)** | Pelacakan otomatis dimatikan (*killed by system*) di luar jam kerja (pukul 17:00). |
| **Banjir Sanggahan KPI** | Masa Sanggah (*Dispute*) hanya terbuka tanggal 1-3. Sanggahan dibatasi 1 kali ke Kasi. Keputusan Kasi bersifat final (*lock*). |

---

## 5. UI/UX Mapping (Tata Letak Halaman)

### 5.1. Aplikasi Mobile (Capacitor/Vue)
Navigasi *Bottom Bar* (5 Menu Utama):
1.  **Beranda:** Widget fase bulan berjalan, Skor Pakaian (100), Skor Integritas (100). Peta Mini GPS *user* (radius hijau), dan Tombol Raksasa Absen Masuk/Pulang.
2.  **Target Kinerja:** Form pengajuan KPI (awal bulan) dan *slider* evaluasi (akhir bulan).
3.  **Lapor Pelanggaran (Tombol Tengah):** Form potret kamera/unggah, *dropdown* nama pegawai, *dropdown* kategori (Pakaian/Integritas).
4.  **Tim & Pantauan (Dinamis):**
    * *Staf:* Peta sesama staf di 1 Bagian, form evaluasi sejawat.
    * *Kasi/Kabag:* Peta bawahan, tombol *switch* ke sesama pejabat, tombol "Lihat Logbook Bawahan", dan antrean *Approval* (Absen Telat, Rencana Kerja, Sanggah, Aduan).
5.  **Profil & Logbook:** Info Pegawai, pengajuan Cuti/Sakit, arsip nilai (tombol Sanggah), dan *Timeline feed* Logbook harian.

### 5.2. Web Panel Admin (Filament PHP)
Navigasi *Sidebar* Kiri:
1.  **Dashboard:** Metrik kehadiran, diagram status KPI (Draft/Evaluasi/Sanggah).
2.  **Organisasi & Pegawai:** CRUD departemen, data seluruh pegawai (fitur mutasi, ubah atasan).
3.  **KPI & Evaluasi:** Kontrol periode (buka/tutup bulan), tabel nilai akhir, dan tombol ekspor Excel/PDF.
4.  **Kehadiran HR:** Input manual/Dinas Luar. Tabel klik *Approve* untuk mengubah cuti kertas menjadi sah di sistem.
5.  **Dapur Kepegawaian:** Input status instansi (Koperasi vs Perumdam) dan manajemen Kepanitiaan (SK/Acara) untuk referensi promosi jabatan.

---

## 4. Struktur Folder & Arsitektur (MVC + API)

Karena aplikasi memisahkan Web (Blade) dan Mobile (Capacitor via API), Anda harus memisahkan *Controller* dengan tegas agar logika tidak tumpang tindih.

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Web/                  <-- (Khusus untuk render view Blade panel HRD)
│   │   │   ├── DashboardController.php
│   │   │   ├── EmployeeController.php
│   │   │   ├── LeaveRequestController.php
│   │   │   └── KpiPeriodController.php
│   │   ├── Api/                  <-- (Khusus JSON Response untuk Capacitor)
│   │   │   ├── AuthController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── KpiPlanController.php
│   │   │   ├── TrackingController.php
│   │   │   └── ViolationController.php
│   ├── Requests/                 <-- (Form Request Validation terpisah)
│   ├── Resources/                <-- (API JsonResources untuk format response API)
├── Services/                     <-- (SINGLE SOURCE OF TRUTH untuk kalkulasi)
│   ├── AttendanceService.php
│   ├── KpiEvaluationService.php
│   └── LocationService.php
├── Models/
│   ├── User.php, Attendance.php, KpiPlan.php, dll.

resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php         <-- (Master layout TailAdmin/Flux)
│   ├── admin/
│   │   ├── dashboard/index.blade.php
│   │   ├── employees/index.blade.php, create.blade.php, edit.blade.php
│   │   ├── hris/leaves.blade.php
│   │   └── kpi/periods.blade.php


5. Roadmap & Tahapan Pembuatan (Task List)Fase ini dirancang berurutan. Jangan melompat ke pembuatan UI Vue sebelum API diuji menggunakan Postman atau Scramble.Fase 1: Inisialisasi & Fondasi BackendJalankan laravel new kpi-hris (Pilih Git, jangan install starter kit berlebih jika ingin pure blade, atau install Breeze basic untuk auth scaffolding).Konfigurasi .env (DB & Redis).Install Scramble (composer require dedoc/scramble) untuk auto-dokumen API.Install Sanctum (php artisan install:api) untuk token mobile.Install Laravel Reverb (php artisan install:broadcasting).Fase 2: Skema Database & Relasi (Migration & Models)Buat Migration & Model untuk departments, locations, users.Buat Migration & Model untuk attendances, leave_requests, user_current_locations.Buat Migration & Model untuk kpi_periods, kpi_period_indicators, kpi_plans, kpi_evaluations.Buat Migration & Model untuk violation_reports, kpi_disputes, kpi_final_scores.Buat Migration & Model untuk daily_activities, committees, committee_members.Tahap Testing: Buat Seeder dan Factory. Uji relasi atasan-bawahan asimetris (Kasi, Solo IT, Staf Ahli) menggunakan php artisan tinker.Fase 3: Pengembangan Web Panel (Laravel Blade)Setup UI Library (TailAdmin atau Flux) di resources/views/layouts/app.blade.php.Buat Middleware role:admin agar hanya HRD yang bisa akses rute web.php (prefix /admin).Slicing & CRUD Web\EmployeeController (Master Pegawai).Slicing & CRUD Web\KpiPeriodController (Pengaturan bulan).Slicing View Web\LeaveRequestController (Halaman validasi kertas cuti $\rightarrow$ klik Approve panggil AttendanceService).Buat logika halaman Dashboard (Hitung count telat, absen hari ini).Fase 4: Pengembangan REST API (Untuk Mobile)Buat rute di routes/api.php dan lindungi dengan middleware auth:sanctum.Buat Api\AuthController (Login menghasilkan Token). Test via Scramble (/docs/api).Buat Api\AttendanceController (Validasi koordinat GPS HP dengan koordinat locations, inject is_apel).Buat Api\KpiPlanController (Input target bulan berjalan).Buat Api\ViolationController (Unggah aduan perilaku).Buat Api\TrackingController (Endpoint yang menembak Redis dan memicu event Reverb).Tahap Testing: Pastikan semua respon API berformat standar (status, pesan, data).Fase 5: Inisialisasi Mobile Frontend (Vue & Capacitor)Buat project baru npm create vue@latest kpi-mobile.Install Tailwind CSS & DaisyUI untuk UI komponen mobile.Install Vue Router & Pinia (State Management).Install Capacitor CLI & Core (npm i @capacitor/core @capacitor/cli).Inisialisasi Capacitor (npx cap init), lalu tambahkan platform (npx cap add android).Install Plugin Native: @capacitor/geolocation, @capacitor/camera, @capacitor/preferences (untuk simpan token Sanctum).Fase 6: Slicing UI & Integrasi Mobile (Tahap Akhir)Slicing UI Login View & Bottom Navigation.Buat Pinia Store useAuthStore untuk mengatur token login dan logout otomatis jika 401.Slicing Home View & Integrasikan API Absensi (tombol Check-in/out).Slicing UI Mini Map di Beranda menggunakan Leaflet JS atau Google Maps SDK.Slicing UI Pelaporan (Akses @capacitor/camera dengan limitasi source hanya kamera, bukan galeri untuk pelanggaran Pakaian).Slicing UI Timeline Logbook (Aktifitas Harian).Integrasi Laravel Echo / Reverb Client di Vue untuk menangkap pergerakan peta Live Tracking.Tahap Testing End-to-End: Run di Android Emulator (npx cap run android). Tes GPS palsu (Mock Location), tes batas jam apel, tes pemotongan nilai default 100 di akhir bulan.

// ==========================================
// 1. ORGANISASI & MASTER PENGGUNA
// ==========================================
Table departments {
  id int [pk, increment]
  name varchar
  type varchar [note: "ENUM: PUSAT, CABANG, UNIT, SPI"]
}

Table locations {
  id int [pk, increment]
  name varchar
  lat decimal(10, 8)
  long decimal(11, 8)
  radius_meters int
}

Table users {
  id int [pk, increment]
  nik varchar [unique]
  name varchar
  department_id int [ref: > departments.id]
  
  job_level int [note: "1=Direksi, 2=Kabag/Kacab/Kanit/StafAhli, 3=Kasi, 4=Staf"]
  direct_supervisor_id int [ref: > users.id, null]
  final_supervisor_id int [ref: > users.id, null]
  
  instansi varchar [note: "ENUM: PERUMDAM_TD, KOPKARTIRTA"]
  employment_status varchar [note: "ENUM: MAGANG, KONTRAK, TETAP"]
  leave_balance int [default: 12]
}

// ==========================================
// 2. HRIS: ABSENSI, CUTI & PELACAKAN LOKASI
// ==========================================
Table attendances {
  id int [pk, increment]
  user_id int [ref: > users.id]
  date date
  clock_in datetime
  clock_out datetime
  
  is_apel boolean [default: false, note: "Otomatis TRUE jika clock_in <= 08:00"]
  status varchar [note: "ENUM: HADIR, TELAT, PULANG_CEPAT, ALPA, CUTI, DINAS_LUAR, SAKIT"]
  late_reason text
  supervisor_approval varchar [default: "PENDING", note: "ENUM: PENDING, APPROVED, REJECTED"]
}

Table leave_requests {
  id int [pk, increment]
  user_id int [ref: > users.id]
  type varchar [note: "ENUM: CUTI, SAKIT, DINAS_LUAR"]
  start_date date
  end_date date
  attachment_path varchar
  hr_final_status varchar [default: "PENDING", note: "ENUM: PENDING, APPROVED, REJECTED (Trigger insert ke attendances)"]
}

Table user_current_locations {
  user_id int [pk, ref: - users.id, note: "Strict 1-to-1 untuk Live Tracking"]
  lat decimal(10, 8)
  long decimal(11, 8)
  last_updated_at datetime [note: "Dasar penentuan flag kuning/offline jika > 3 menit"]
}

// ==========================================
// 3. REKAM JEJAK: LOGBOOK & KEPANITIAAN
// ==========================================
Table daily_activities {
  id bigint [pk, increment]
  user_id int [ref: > users.id]
  kpi_plan_id int [ref: > kpi_plans.id, null, note: "Tautan opsional ke target bulanan"]
  
  activity_date date [note: "Dibatasi H-2 untuk mencegah rekayasa"]
  description text
  photo_path varchar [null]
  created_at datetime
}

Table committees {
  id int [pk, increment]
  sk_number varchar [unique]
  event_name varchar
  start_date date
  end_date date
}

Table committee_members {
  id int [pk, increment]
  committee_id int [ref: > committees.id]
  user_id int [ref: > users.id]
  role_in_committee varchar
}

// ==========================================
// 4. SISTEM KPI BULANAN
// ==========================================
Table kpi_periods {
  id int [pk, increment]
  month int
  year int
  status varchar [note: "ENUM: DRAFT, EVALUATION, DISPUTE, CLOSED"]
}

Table kpi_plans {
  id int [pk, increment]
  user_id int [ref: > users.id]
  period_id int [ref: > kpi_periods.id]
  target_description text
  weight decimal [note: "Akumulasi bobot per user maks 50"]
  
  self_assessment_score int
  self_assessment_note text
  status varchar [default: "DRAFT", note: "ENUM: DRAFT, APPROVED, REJECTED"]
}

Table kpi_evaluations {
  id int [pk, increment]
  kpi_plan_id int [ref: > kpi_plans.id]
  evaluator_id int [ref: > users.id]
  evaluator_role varchar [note: "ENUM: KASI, KABAG, REKAN, KANIT_SOLO"]
  score int
}

// ==========================================
// 5. SISTEM ADUAN 360-DERAJAT (PENGURANG NILAI 100)
// ==========================================
Table violation_reports {
  id int [pk, increment]
  reported_user_id int [ref: > users.id]
  reporter_id int [ref: > users.id]
  period_id int [ref: > kpi_periods.id]
  
  category varchar [note: "ENUM: PAKAIAN_DINAS, INTEGRITAS"]
  description text [note: "Wajib jika kategori Integritas"]
  photo_path varchar [note: "Wajib jika kategori Pakaian Dinas"]
  incident_date date
  
  status varchar [default: "PENDING", note: "ENUM: PENDING, VALIDATED, REJECTED"]
  deduction_point int [default: 0]
}

// ==========================================
// 6. RESOLUSI KONFLIK (MASA SANGGAH)
// ==========================================
Table kpi_disputes {
  id int [pk, increment]
  period_id int [ref: > kpi_periods.id]
  user_id int [ref: > users.id]
  kpi_evaluation_id int [ref: > kpi_evaluations.id]
  
  reason text
  evidence_file_path varchar
  
  status varchar [default: "PENDING", note: "ENUM: PENDING, ACCEPTED, REJECTED"]
  resolution_note text
  revised_score int
}

// ==========================================
// 7. ARSIP NILAI FINAL (BUCKETS)
// ==========================================
Table kpi_final_scores {
  id int [pk, increment]
  user_id int [ref: > users.id]
  period_id int [ref: > kpi_periods.id]
  
  score_kinerja decimal [note: "Bobot 50%. Kalkulasi evaluasi vertikal & horizontal"]
  score_kehadiran decimal [note: "Bobot 20%. Rasio Kehadiran HRIS"]
  score_apel decimal [note: "Bobot 5%. Rasio ikut apel"]
  score_pakaian decimal [note: "Bobot 5%. Default 100 dikurangi penalti pakaian"]
  score_integritas decimal [note: "Bobot 20%. Default 100 dikurangi penalti integritas"]
  
  grand_total_score decimal [note: "Total Akhir (Maksimal 100)"]
}
