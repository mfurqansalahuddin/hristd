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

Nilai akhir kinerja **bukan dari 1 orang** — dihitung dari **Penilai 1 & 2**
(update 2026-07-20: Penilai 3/rekan sejawat tidak lagi ikut menilai Kinerja,
dialihkan penuh ke Integritas — lihat §5), seragam jumlahnya di semua level
jabatan (`job_level`), bobot disimpan di master `kpi_evaluator_weights`:

| Level | Penilai 1 | Penilai 2 |
|---|---|---|
| Staf | Kasi — 50% | Kabag — 50% |
| Kasi | Kabag — 50% | Direktur Bidang — 50% |
| Kabag-setara | Direktur Bidang — 50% | Direktur Utama — 50% |

Penilai 3 tetap dihitung oleh `EvaluatorResolutionService` (dipakai untuk
Integritas, §5) tapi bobotnya di `kpi_evaluator_weights` diset 0 untuk Kinerja
— baris tidak dihapus, cuma tidak berkontribusi. Riwayat: sebelum 2026-07-20
ketiga slot menilai Kinerja seragam 33/33/34; sebelum itu lagi Kasi sempat
dinilai 4 orang (25/25/25/25) tapi dibatalkan.

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

**(Update 2026-07-20) Ini terpisah dari kuota `users.leave_balance`.**
Khusus tipe CUTI, `injectAttendanceForApprovedLeave()` juga memotong
`leave_balance` — tapi sebesar **hari kerja saja** (Senin-Sabtu,
`AttendanceService::workingDaysBetween()`), bukan seluruh rentang kalender
di atas. Jadi cuti 4 hari kalender yang mencakup 1 hari Minggu memotong
jatah cuti 3, walau `attendances` tetap terisi untuk keempat harinya
(termasuk Minggu — harmless untuk skor kehadiran karena rasio di-cap 1).
Sakit/Izin/Dinas Luar tidak memotong `leave_balance` sama sekali.

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

**(Revisi 2026-07-20)** Dulu 1 sumber (aduan atasan langsung saja). Kini
digabung dari **4 sumber berbobot** (default 25/25/25/25, master
`kpi_integrity_source_weights`, admin-editable):

1. **Penilai 1**, **Penilai 2**, **Penilai 3** (rekan seksi acak — dibebaskan
   dari Kinerja, §1) — masing-masing **wajib** mereview tiap pegawai yang
   jadi tanggung jawabnya, per periode, lewat `kpi_integrity_evaluations`.
   Untuk tiap satu dari 8 sub-kategori (tabel di bawah), evaluator memilih
   **BIARIN** (tidak ada temuan) atau **KURANGIN** (ada temuan) — memilih
   KURANGIN mewajibkan `description` + `photo`.
2. **Aduan Perusahaan** — siapa saja di seluruh Perumda boleh melapor (bukan
   lagi dikunci ke atasan langsung), lewat `violation_reports` category
   INTEGRITAS seperti model lama, divalidasi lewat panel admin **Validasi
   Aduan**.

Tiap sumber dihitung skornya **sendiri-sendiri** (0-100) dengan formula yang
sama, lalu digabung berbobot:

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

Untuk 1 sumber: tiap kategori mulai dari skor penuh (`deduction_value`-nya
sendiri). Temuan/KURANGIN tervalidasi dari sumber itu di kategori tsb
men-nol-kan kategori itu **untuk sumber itu saja** (floor 0, tidak minus,
flat per kejadian — bukan berjenjang, tidak menyebar ke 3 sumber lain).
Khusus sumber Aduan Perusahaan, dedup harian tetap berlaku (kejadian sama,
orang sama, hari sama → dihitung 1×).

```
skor 1 sumber   = Σ (skor kategori tersisa untuk sumber itu, floor 0)   // maks 100
skor integritas = Σ (skor sumber × bobot sumber / 100)                  // rata-rata tertimbang 4 sumber
                  dikonversi proporsional ke bobot 20%
```

Pegawai yang belum direview evaluator tertentu di periode berjalan
dihitung skor penuh (100) untuk sumber itu — sama seperti kategori yang
belum pernah dilaporkan, bukan diblokir/dianggap 0.

---

## Ringkasan alur "aduan" (Pakaian Dinas vs Integritas — sumber Aduan Perusahaan)

Satu tabel `violation_reports`, dibedakan kolom `category`. **(Update
2026-07-20)** Untuk Integritas, tabel ini sekarang cuma jadi salah satu dari
4 sumber ("Aduan Perusahaan") — Penilai 1/2/3 pindah ke `kpi_integrity_evaluations`
(§5), bukan lagi lewat `violation_reports`.

| | Pakaian Dinas | Integritas (sumber Aduan Perusahaan) |
|---|---|---|
| Siapa boleh lapor | Siapa saja | Siapa saja (dulu terkunci ke atasan langsung, sekarang terbuka) |
| Wajib foto? | Ya | Tidak (wajib deskripsi + pilih 1 dari 8 kategori) |
| Validasi | Panel admin **Validasi Aduan** | Panel admin **Validasi Aduan** (dulu atasan langsung terlapor per-laporan) |
| Efek ke skor | Potong komponen Pakaian Dinas (5%) | Potong kategori terkait, hanya untuk sumber "Aduan Perusahaan" (1 dari 4 sumber Integritas) |

Form input aduan di rencana mobile ada di §14.5 sub-tab "Aduan Disiplin
Pakaian & Integritas" — dropdown nama dicari, kalau kategori dipilih
Integritas maka daftar sub-kategori (8 di atas) baru muncul. Server tidak
lagi mengecek relasi atasan-bawahan (2026-07-20).

---

## Kapan skor benar-benar dihitung

Belum ada trigger otomatis. Rencananya (`Fase C`): saat HRD mengubah
`kpi_periods.status` ke `CLOSED` lewat `KpiPeriodController::updateStatus`,
itu harus memicu `KpiEvaluationService` menjumlahkan 5 komponen di atas dan
menulis hasilnya ke `kpi_final_scores`. Saat ini transisi status periode
**belum** punya efek samping apa pun — murni ubah label status.
