# Logika Perhitungan KPI

Sumber: `plan.md` §5, §7-§8, §16 + model aktual di `app/Models`, `app/Services`.
Skor akhir bulanan = **5 komponen, total 100**. Disimpan di `kpi_final_scores`,
bobot tiap komponen di-master-data-kan di tabel `kpi_component_weights` (bukan
hard-code, HRD bisa ubah tanpa deploy).

| # | Komponen | Bobot |
|---|----------|-------|
| 1 | Kinerja Teknis (rencana kerja) | 50% |
| 2 | Kehadiran | 20% |
| 3 | Apel Pagi | 5% |
| 4 | Pakaian Dinas | 5% |
| 5 | Integritas | 20% |

✅ **Status kode (2026-07-16):** `KpiEvaluationService` (`app/Services/KpiEvaluationService.php`)
sudah dibangun dan menjumlahkan skor sesuai logika di dokumen ini — dipicu
otomatis saat HRD mengubah status periode ke `CLOSED` di panel admin
(`Livewire\Admin\KpiPeriodsTable::updateStatus`). Lihat juga
`tests/Feature/KpiEvaluationServiceTest.php`.

---

## 1. Kinerja Teknis (50%) — nilai rencana kerja

Pegawai isi **rencana kerja** (`kpi_plans`) tiap bulan: daftar target + bobot
per target (total bobot ≤ 50, dikunci HRD saat masa pengajuan ditutup).
Rencana ini harus di-approve atasan pertama dulu (`kpi_plan_reviews`) sebelum
masuk fase "Working".

Nilai akhir kinerja **bukan dari 1 orang** — dihitung dari 3 **penilai**,
seragam jumlahnya di semua level jabatan (`job_level`), bobot disimpan di
master `kpi_evaluator_weights`:

| Level | Penilai 1 | Penilai 2 | Penilai 3 |
|---|---|---|---|
| Staf | Kasi — 33% | Kabag — 33% | Rekan seksi — 34% |
| Kasi | Kabag — 33% | Direktur Bidang — 33% | Rekan sesama Kasi — 34% |
| Kabag-setara | Direktur Bidang — 33% | Direktur Utama — 33% | Rekan sesama Kabag — 34% |

Kasi sempat dinilai 4 orang (ditambah Direktur Utama, 25/25/25/25) tapi
dibatalkan — kembali ke 3 penilai seperti di atas.

Tiap penilai kasih skor per target di `kpi_evaluations`. Skor akhir komponen
Kinerja = jumlah **(skor penilai × bobot slot penilai)**, lalu dikonversi ke
skala 50%.

**Siapa penilainya dihitung on-the-fly**, bukan disimpan sebagai FK statis di
`users` (kolom `direct_supervisor_id` sudah dihapus). Penentuannya murni dari
`department_id` + `job_level` pegawai saat itu, lewat `EvaluatorResolutionService`
(§5 `plan.md`, ✅ sudah dibangun — `app/Services/EvaluatorResolutionService.php`).
Aturan fallback-nya:

- Kasi kosong → Penilai 1 turun ke Kabag (Penilai 2 tetap Kabag, jadi Kabag
  bisa merangkap 2 slot — itu sah, tidak ada logika anti-dobel).
- Kasi kosong **dan** staf sendirian di seksi → Kabag mengisi ketiga slot,
  100% dinilai Kabag.
- Rekan sejawat (slot penilai terakhir tiap level) dirotasi tiap periode
  (siklus A→B→C→A) supaya tidak ada A menilai B dan B menilai A di periode
  yang sama — kecuali grup cuma 2 orang, di situ saling menilai memang
  diizinkan.

Direksi (job_level 1) di luar cakupan KPI bulanan ini.

---

## 2. Kehadiran (20%)

Rasio hari hadir dihitung otomatis dari tabel `attendances`:

```
skor kehadiran = (jumlah hari status hadir/cuti-approved) / (jumlah hari kerja dalam periode) × 100
```

### Bagaimana Cuti / Sakit / Izin (Dinas Luar) masuk hitungan

**Cuti, Sakit, dan Dinas Luar tidak mengurangi skor kehadiran** — begitu
`leave_requests` disetujui, `AttendanceService::injectAttendanceForApprovedLeave()`
otomatis menulis baris `attendances` untuk tiap tanggal dalam rentang cuti,
dengan `status` = jenis izin itu sendiri (`CUTI`/`SAKIT`/`DINAS_LUAR`) dan
`supervisor_approval = APPROVED` — dianggap **hadir 100%** di hari itu, bukan
absen. Kode aktualnya:

```php
// app/Services/AttendanceService.php
foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
    Attendance::updateOrCreate(
        ['user_id' => $leaveRequest->user_id, 'date' => $date->toDateString()],
        ['status' => $leaveRequest->type, 'supervisor_approval' => 'APPROVED']
    );
}
```

Yang beda antar ketiganya cuma **siapa yang approve** dan **dokumen wajib**
(§16 `plan.md`), bukan efeknya ke skor KPI — begitu approved, ketiganya sama
saja di mata perhitungan kehadiran:

| Jenis | Yang approve | Dokumen wajib |
|---|---|---|
| Cuti | HR (panel admin) | Scan surat acc Direktur |
| Sakit | Atasan pertama (bukan HR) | Surat dokter, wajib kalau >1 hari |
| Dinas Luar | Tidak ada approval — SPPD fisik sudah jadi otorisasi | SPPD/surat pengantar |

Kalau **tidak** disetujui (`status=REJECTED` atau masih `PENDING`), tidak ada
baris `attendances` yang diinjeksi — hari itu tetap terhitung absen kalau
pegawai memang tidak clock-in.

---

## 3. Apel Pagi (5%)

Khusus hari **Senin** saja (bukan tiap hari kerja). `is_apel = true` otomatis
kalau ada absen tercatat tepat waktu pada Senin itu.

```
skor apel = (jumlah Senin dengan is_apel=true) / (total Senin dalam periode) × 100
```

---

## 4. Pakaian Dinas (5%)

Mulai dari skor **100** per pegawai, dikurangi tiap ada **aduan foto
tervalidasi**. Aduan ini terbuka untuk **siapa saja** melaporkan (§8.4) —
beda dengan Integritas yang terkunci — tapi wajib lampirkan foto. Setelah
divalidasi atasan langsung terlapor, `deduction_point` di `violation_reports`
mengurangi skor komponen ini.

Dedup harian: aduan berulang, kategori sama, orang sama, hari sama →
dihitung 1× saja (diabaikan diam-diam saat `KpiEvaluationService` menghitung,
bukan ditolak saat submit).

---

## 5. Integritas (20%)

Beda dari Pakaian Dinas: **hanya atasan yang boleh mengadu tentang
bawahannya** (dihitung via hierarki §5, sama seperti resolusi penilai),
bukan siapa saja. Wajib deskripsi + pilih 1 dari 8 sub-kategori berikut
(`kpi_integrity_categories`), tiap kategori mulai dari skor penuh:

| Skor Maks | Kategori |
|---|---|
| 10 | Etika |
| 10 | Kerjasama Tim |
| 20 | Jujur dan Transparansi |
| 15 | Loyalitas |
| 10 | Kepatuhan dan Budaya Organisasi |
| 15 | Ketepatan Waktu |
| 10 | Inisiatif |
| 10 | Tugas Tambahan |

Tiap aduan tervalidasi mengurangi skor kategori terkait sebesar
`kpi_integrity_categories.deduction_value` — **flat per kejadian**, bukan
berjenjang (makin sering dilaporkan, potongannya sama besar tiap kali, bukan
makin besar). Skor tidak bisa minus (floor 0). Dedup harian sama seperti
Pakaian Dinas.

```
skor integritas = Σ (skor kategori setelah dikurangi, floor 0)   // dari total maks 100
                 dikonversi proporsional ke bobot 20%
```

---

## Ringkasan alur "aduan" (Pakaian Dinas vs Integritas)

Satu tabel `violation_reports`, dibedakan kolom `category`:

| | Pakaian Dinas | Integritas |
|---|---|---|
| Siapa boleh lapor | Siapa saja | Hanya atasan langsung ke bawahan |
| Wajib foto? | Ya | Tidak (wajib deskripsi + pilih 1 dari 8 kategori) |
| Validasi | Atasan langsung terlapor | Atasan langsung terlapor |
| Efek ke skor | Potong komponen Pakaian Dinas (5%) | Potong salah satu dari 8 sub-kategori Integritas (20%) |

Form input aduan di rencana mobile ada di §14.5 sub-tab "Aduan Disiplin
Pakaian & Integritas" — dropdown nama dicari, kalau kategori dipilih
Integritas maka daftar sub-kategori (8 di atas) baru muncul dan sistem
mengecek dulu apakah pelapor memang atasan langsung terlapor.

---

## Kapan skor benar-benar dihitung

Belum ada trigger otomatis. Rencananya (`Fase C`): saat HRD mengubah
`kpi_periods.status` ke `CLOSED` lewat `KpiPeriodController::updateStatus`,
itu harus memicu `KpiEvaluationService` menjumlahkan 5 komponen di atas dan
menulis hasilnya ke `kpi_final_scores`. Saat ini transisi status periode
**belum** punya efek samping apa pun — murni ubah label status.
