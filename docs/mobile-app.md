# Aplikasi Mobile — Spesifikasi Layar & Kebutuhan API

Sumber: `docs/plan.md` §14-§16. Dokumen ini mengelompokkan ulang per **halaman**:
apa yang tampil + data apa yang dibutuhkan, lalu API apa saja yang harus
dipanggil untuk mengisinya.

**Stack:** Capacitor JS + Vue. Auth: token Sanctum (`POST /api/login` terima
email/NIK/username + password). Semua halaman butuh token kecuali Login.

⚠️ **Status keseluruhan (2026-07-16):** seluruh endpoint yang didokumentasikan
di file ini **sudah dibangun** (Fase D selesai) — lihat `routes/api.php` +
tabel status per endpoint di tiap bagian di bawah, semuanya ✅. Aplikasi
mobile-nya sendiri (Capacitor+Vue, Fase E) **masih belum ada project-nya** —
endpoint-endpoint ini sudah bisa dites lewat Postman/Scramble (`/docs/api`)
sambil Fase E menyusul. Yang masih dokumentasi murni: mobile UI-nya sendiri
(layar, komponen Vue) dan channel Reverb privat utk live location (§8 poin 6).

---

## 1. Login

**Tampilan:** form identifier (email/NIK/username) + password. Sukses →
simpan token, redirect ke Home.

**API:**

| Endpoint          | Status | Request               | Response          |
| ----------------- | ------ | --------------------- | ----------------- |
| `POST /api/login` | ✅ ada | `{ login, password }` | `{ token, user }` |

---

## 2. Home (Halaman Utama)

**Tampilan & data:**

- Sapaan dinamis "Selamat pagi/siang/sore, {nama}" — dihitung dari jam device, tidak perlu API.
- Tanggal & jam — device clock, tidak perlu API.
- Minimap kecil posisi user saat ini (Leaflet, sudah ada di `package.json`).
- Tombol **Absen Masuk** / **Absen Keluar** — nonaktif otomatis kalau sudah absen hari itu untuk arah yang sama.
- Tombol **Refresh Lokasi** — ping GPS manual, terpisah dari auto-ping tiap 1 menit (§4 di bawah).

### 2.1 Baris status rencana kerja / penilaian (`kpi_phase.my_step`)

**(2026-07-16)** 1 baris teks, isinya beda tergantung fase periode + posisi
user di alur itu. Warning (kuning) kalau ada tindakan tertunda di pihak
user, teks netral kalau tinggal menunggu:

| Fase periode           | `my_step`               | Teks yang tampil                                                                                                                     |
| ---------------------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| Draft (masa pembuatan) | `NOT_STARTED`           | ⚠️ "Anda belum membuat rencana kerja bulanan"                                                                                        |
| Draft                  | `SUBMITTED`             | "Menunggu approval rencana kerja"                                                                                                    |
| Draft                  | `REVISION_REQUESTED`    | 🔴 "Revisi rencana kerja dari atasan" — banner **merah**, beda dari warning kuning biasa. Tap → sub-tab 5.1 KPI Bulanan (lihat §5.3) |
| Draft                  | `APPROVED`              | "Rencana kerja sudah dibuat dan disetujui"                                                                                           |
| Working                | `NEEDS_SELF_ASSESSMENT` | ⚠️ "Anda belum input hasil kinerja anda"                                                                                             |
| Working/Evaluation     | `WAITING_EVALUATION`    | "Menunggu penilaian atasan"                                                                                                          |
| Evaluation/Dispute     | `EVALUATED`             | "Anda sudah dinilai" + (kalau window sanggah terbuka & job_level Kasi/Kabag) ", masa sanggah dibuka"                                 |
| Closed                 | `CLOSED`                | "KPI periode {bulan} ditutup" + skor akhir                                                                                           |

Tap baris ini (kecuali state `CLOSED`) → masuk ke sub-tab 5.1 KPI Bulanan.

### 2.2 Baris jumlah rekan kerja & bawahan yang perlu dinilai

**(2026-07-16)** 2 baris terpisah (bukan digabung):

- "Ada **{jumlah}** rekan kerja yang perlu dinilai" atau "Anda sudah menilai
  semua rekan anda" — dari slot Penilai 3 (rekan sejawat, §1
  `kpi-calculation.md`).
- "Ada **{jumlah}** bawahan anda yang perlu dinilai" atau "Anda telah
  menilai semua bawahan anda" — dari slot Penilai 1/2 (hierarki atasan).

Keduanya tap → sub-tab 5.4 Beri Penilaian. Selama masih ada yang belum
dinilai, push notif pengingat **2× sehari (pagi & sore)** — bukan tiap jam,
supaya tidak terasa spam (dikonfirmasi user 2026-07-16).

### 2.3 Ikon lonceng — pengajuan Sakit bawahan

**(2026-07-16)** Muncul hanya kalau user punya bawahan **dan** ada
pengajuan Sakit `PENDING` menunggu approval-nya (§7.3). Tap → langsung ke
sub-tab Sakit-Approval.

**API:**

| Endpoint                         | Status            | Request         | Response                                                                                                                                                                                                                                               |
| -------------------------------- | ----------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `GET /api/user`                  | ✅ ada, diperkaya | —               | `User` + `department`, `jabatan_label`, `photo_url`                                                                                                                                                                                                    |
| `GET /api/home`                  | ✅ ada            | —               | `{ greeting_name, kpi_phase: {period, status, my_step}, last_month_score, attendance_today: {clocked_in, clocked_out}, pending_evaluations: {peers, subordinates}, pending_sick_approvals }` — 1 call gabungan untuk §2.1-2.3, hindari N call terpisah |
| `POST /api/attendance/clock-in`  | ✅ ada            | `{ lat, long }` | lihat §3                                                                                                                                                                                                                                               |
| `POST /api/attendance/clock-out` | ✅ ada            | `{ lat, long }` | lihat §3                                                                                                                                                                                                                                               |
| `POST /api/location/ping`        | ✅ ada            | `{ lat, long }` | lihat §4                                                                                                                                                                                                                                               |

Push notif 2×/hari (§2.2) dan badge lonceng (§2.3) butuh infra push —
⚠️ **asumsi:** untuk awal cukup badge/banner in-app (polling `GET
/api/home`), push notifikasi OS asli (FCM/APNs) belum dianggap tersedia —
aplikasinya sendiri belum ada project-nya (lihat catatan status di atas),
jadi infra push realistis menyusul Fase D/E, bukan prasyarat awal.

---

## 3. Riwayat Absen (1 Bulan)

**Tampilan & data:** list per tanggal, **model tindih atas-bawah**
(2 baris per tanggal, bukan 2 kolom bersebelahan) — filter bulan, default
bulan berjalan:

```
12/07/2026
  Masuk   08:15  •  Kantor Pusat        •  Terlambat
  Keluar  16:30  •  Kantor Pusat        •  Tepat Waktu
```

Baris ketiga (badge status) tampil merah + "Menunggu approval HR" kalau
kondisinya telat/pulang cepat/luar geofence dan HR belum approve (§8.1
`plan.md`) — badge lokasi dalam/luar geofence (nama lokasi kalau cocok, atau
"Luar Lokasi Kantor").

⚠️ **(2026-07-16)** Approve/reject untuk kondisi telat/cepat/luar-geofence
**bukan** dilakukan dari mobile — itu sepenuhnya wewenang **HR** lewat
tombol baru di tab admin Kehadiran (web, §8.1 `plan.md`). Layar ini di
mobile murni **baca status**, tidak ada tombol aksi.

**API:**

| Endpoint                                   | Status | Request                                        | Response                                                                                                                                                                                                                                             | Catatan                                                                                               |
| ------------------------------------------ | ------ | ---------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| `POST /api/attendance/clock-in`            | ✅ ada | `{ lat, long, approval_reason? }`              | Row `attendances` baru: `status` via `Attendance::statusMasuk()`, `is_apel` otomatis kalau Senin & tepat waktu, `matched_location`. Kalau telat/luar geofence → wajib `approval_reason`, `supervisor_approval = PENDING` (menunggu HR, bukan atasan) | Kolom `late_reason` di-rename jadi `approval_reason` (2026-07-16, cakupannya melebar dari cuma-telat) |
| `POST /api/attendance/clock-out`           | ✅ ada | `{ lat, long, approval_reason? }`              | Update row hari ini: `clock_out`, `clock_out_lat/long`, status pulang. Kalau pulang cepat/luar geofence → `supervisor_approval = PENDING` juga                                                                                                       | Tolak kalau belum clock-in hari itu / sudah clock-out                                                 |
| `GET /api/attendance/history?month=&year=` | ✅ ada | query `month`, `year` (default bulan berjalan) | `{ data: [...] }` list absen sebulan + badge lokasi + status approval                                                                                                                                                                                |                                                                                                       |

---

## 4. Live Location

**Tampilan & data:** peta menampilkan posisi rekan kerja terkini. Visibility
beda per level:

| Level                      | Default (`scope=peers`)                                                      | Switch/filter tersedia                                                                                                                                                                                                                                    |
| -------------------------- | ---------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Staf (job_level 4)         | Rekan **1 seksi/departemen yang sama**. Tidak bisa lihat pejabat sama sekali | Tidak ada switch                                                                                                                                                                                                                                          |
| Kasi (job_level 3)         | Seluruh pejabat se-Perumdam, lintas bagian & level                           | Switch "Lihat Bawahan" (`scope=subordinates`) → **(2026-07-16)** diperluas jadi **seluruh staf di 1 Bagian induknya**, bukan cuma Seksi yang dia pimpin sendiri (mis. Kasi Sekretariat bisa lihat staf Seksi Perlengkapan, keduanya di bawah Bagian Umum) |
| Kabag-setara (job_level 2) | Seluruh pejabat se-Perumdam, lintas bagian & level                           | Switch "Lihat Bawahan" (`scope=subordinates`) → staf di Bagian yang dia pimpin (sudah otomatis 1 bagian penuh, tidak berubah)                                                                                                                             |
| Direksi (job_level 1)      | Tidak perlu absen/KPI, tapi tetap pakai layar ini — lihat §4.1 di bawah      | Filter khusus, bukan switch 2-mode seperti level lain                                                                                                                                                                                                     |

### 4.1 Filter khusus Direksi

**(2026-07-16, dikonfirmasi — direvisi dari draf 3-filter sebelumnya)**
Direksi tidak absen dan tidak masuk cakupan KPI, jadi filter "Lintas
Direktorat" (= `scope=peers` biasa) tidak relevan sebagai opsi terpisah buat
mereka — dihapus. Tersisa **2 filter**, default **Pejabat**:

1. **Pejabat** (default) — Kabag-setara & Kasi saja (job_level 2-3), exclude sesama Direksi.
2. **Seluruh Perumdam** — job_level 1-4 (termasuk staf), dengan **sub-filter per Bagian** (pilih 1 Bagian/Cabang/Unit dari dropdown) supaya peta tidak penuh sesak menampilkan semua orang sekaligus.

Param `level_filter=kabag_kasi|everyone`, ditambah `department_id` (opsional,
hanya efektif kalau `level_filter=everyone`) untuk sub-filter Bagian. Hanya
berlaku untuk job_level 1, diabaikan/403 untuk level lain.

Update posisi dari client: tiap 1 menit **atau** tiap pergerakan ≥10 meter,
mana lebih dulu tercapai. GPS berhenti otomatis di luar jam kerja (>17:00).

**API:**

| Endpoint                                                                              | Status | Request                                                                                                                       | Response                                                                                               | Catatan                                                                                   |
| ------------------------------------------------------------------------------------- | ------ | ----------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------- |
| `POST /api/location/ping`                                                             | ✅ ada | `{ lat, long }`                                                                                                               | `{ ok: true }`                                                                                         | Upsert `user_current_locations` (overwrite). No-op server-side di luar jam kerja (>17:00) |
| `GET /api/location/colleagues?scope=peers\|subordinates&level_filter=&department_id=` | ✅ ada | query `scope` (default `peers`; `subordinates` 403 utk Staf), `level_filter`+`department_id` (opsional, hanya Direksi — §4.1) | `{ data: [{ user_id, name, lat, long, last_updated_at, is_online }] }` (`is_online` = update <3 menit) | Untuk Kasi, `scope=subordinates` query ke Bagian induk, bukan Seksi sendiri (§4)          |

Real-time idealnya lewat broadcast Reverb per scope (belum ada channel
selain default `App.Models.User.{id}`) — tanpa itu, layar ini harus polling
`GET /api/location/colleagues`.

---

## 5. KPI Bulanan

Navigasi sub-tab dari bawah. Bar fase di atas: **Draft → Draft Approval →
Working → Evaluation → Dispute (tampilan saja, lihat §5.4) → Final**.

### 5.1 Sub-tab "KPI Bulanan" (rencana kerja + self-assessment)

**Tampilan & data — masa Draft (isi rencana):**

- State awal kosong: tombol **"+ Tambah Rencana Kerja"** di atas, dan
  **(2026-07-16)** tombol kedua di sampingnya **"Salin dari Bulan Lalu"** —
  hanya muncul kalau `GET /api/kpi/plans?period_id=<id bulan lalu>` (endpoint
  yang sama, dipanggil dengan id periode sebelumnya) tidak kosong. Tap →
  langsung panggil `POST /api/kpi/plans/copy-previous`, tanpa form. Cocok
  untuk pekerjaan yang targetnya sama tiap bulan (mis. catat meter) — cukup
  salin lalu edit angkanya kalau perlu.
- Tap "+ Tambah Rencana Kerja" → form muncul: **Nama Kinerja**, **Target
  Kinerja**, **Bobot** → Simpan.
- Item tersimpan tampil sebagai card di list bawah; tap card lagi → form
  yang sama terbuka lagi terisi (mode edit).
- Di dekat tombol Submit, tampilkan total bobot terpakai (mis. "35/50") —
  dihitung dari list yang sudah di-fetch di client, tidak perlu API
  terpisah.
- **(2026-07-16)** Kalau ada `task_requests` (lihat API di bawah) untuk
  periode ini, tampilkan sebagai banner terpisah di atas list — "Instruksi
  tambahan dari atasan: {comment}". Ini beda dari komentar revisi per-item
  (§5.3): ini instruksi supaya pegawai **menambahkan item baru**, bukan
  merevisi item yang sudah ada.
- Tombol **Submit** → kirim semua item untuk approval (§5.3).

**Tampilan & data — masa Working/Evaluation (self-assessment):**

Tab yang sama dipakai lagi (bukan sub-tab baru) begitu plan `APPROVED` dan
periode masuk fase Working/Evaluation:

- Banner teks di atas: "Silakan lakukan self-assessment untuk rencana kerja Anda".
- Tap salah satu item → form **Target Tercapai** (deskripsi) + **Nilai** →
  Simpan.
- Item yang sudah diisi menampilkan badge "Self-assessment tersimpan" di
  bawahnya — simpan per-item, bukan submit sekali di akhir.

**API:**

| Endpoint                                  | Status | Request                                                                                                                                       | Response                                                                                                                                                                              |
| ----------------------------------------- | ------ | --------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /api/kpi/periods/current`            | ✅ ada | —                                                                                                                                             | `{ data: { id, month, year, status } \| null }` — dibungkus `data` karena `response()->json(null)` di Laravel/Symfony tidak pernah mengirim body `null` literal (di-coerce jadi `{}`) |
| `GET /api/kpi/plans?period_id=`           | ✅ ada | query `period_id`                                                                                                                             | `{ data: KpiPlan[], task_requests: KpiPlanReview[] }` milik user — `task_requests` = instruksi `action=TASK_REQUESTED` (2026-07-16, lihat §5.3) untuk periode ini, dipakai juga hitung total bobot di client |
| `POST /api/kpi/plans`                     | ✅ ada | `{ period_id, target_description, weight }` — **1 item per call**, ikut alur tambah-satu-per-satu di UI (bukan array batch seperti draf awal) | Item baru, status DRAFT (422 kalau total bobot >50)                                                                                                                                   |
| `PUT /api/kpi/plans/{id}`                 | ✅ ada | `{ target_description, weight }`                                                                                                              | Item terupdate — hanya kalau status masih DRAFT (403 bukan pemilik, 422 kalau bukan DRAFT/bobot >50)                                                                                  |
| `POST /api/kpi/plans/submit`              | ✅ ada | `{ period_id }`                                                                                                                               | Semua item `DRAFT` milik user → status `SUBMITTED`                                                                                                                                    |
| `POST /api/kpi/plans/copy-previous`       | ✅ ada (2026-07-16) | `{ period_id }`                                                                                                                    | Salin `target_description`+`weight` dari plan periode sebelumnya jadi item baru status DRAFT. 422 kalau periode ini sudah punya plan, atau periode lalu tidak ada plan               |
| `PUT /api/kpi/plans/{id}/self-assessment` | ✅ ada | `{ self_assessment_score, self_assessment_note, self_assessment_photo? }`                                                                     | Item terupdate — isi setelah plan `APPROVED` (422 kalau belum), sebelum evaluator menilai                                                                                             |

### 5.2 Sub-tab "KPI Tahunan"

Placeholder UI "segera hadir" — tanpa backend dulu, tidak perlu API.

### 5.3 Sub-tab "Approval Rencana Kinerja"

**Tampilan & data:** khusus atasan pertama (Staf→Kasi, Kasi→Kabag,
Kabag→Direktur Bidang — **tidak** untuk Dirut). List nama yang perlu
di-approve/direvisi → klik nama → detail per item rencana + kolom komentar +
tombol Approve/Revisi.

**Sisi bawahan saat direvisi:** banner merah di Home "Revisi Kinerja dari
Atasan" (§2.1) → tap → masuk ke sub-tab 5.1 KPI Bulanan.

⚠️ **Saran soal granularitas komentar revisi:** skema `kpi_plan_reviews`
(§10 `plan.md`) saat ini cuma punya **1 kolom `comment` per putaran
approve/revisi** — bukan per-item. Dua opsi:

1. **(Direkomendasikan, tanpa ubah skema)** 1 komentar per putaran,
   ditampilkan sebagai catatan di **atas** list rencana kerja (bukan nempel
   di tiap item) — atasan menyebut nama item yang dimaksud langsung di
   teks komentarnya. Cukup untuk skala tim kecil, tidak perlu migrasi
   tambahan.
2. Kalau memang butuh komentar per-item terpisah: perlu tambah kolom
   `kpi_plan_id` (nullable) ke `kpi_plan_reviews`, jadi 1 putaran revisi
   bisa punya banyak baris komentar (1 per item yang direvisi). Lebih rapi
   tapi nambah 1 migration + form atasan jadi lebih ribet (komentar
   per-item, bukan 1 kotak teks).

Dokumen ini mengasumsikan **opsi 1** kecuali diputuskan lain.

⚠️ **(2026-07-16)** Selain komentar revisi di atas, atasan pertama juga bisa
kirim **komentar terpisah** dengan makna beda: "tolong tambahkan 1 item
rencana kerja baru di luar yang sudah ada" — dipakai untuk kasus ada tugas
tambahan mendadak di luar rencana awal pegawai. Ini **bukan** revisi item
yang sudah ada (dipisah dari `comment` di `POST /api/kpi/approvals/{userId}`,
yang tetap berarti "revisi item ini/rencana ini"), dan **tidak mengubah
status** `kpi_plans` — murni catatan yang muncul di sisi pegawai sebagai
`task_requests` (§5.1). Tombol "Minta Tambah Item" di layar detail approval,
terpisah dari tombol Approve/Revisi, bisa dipakai kapan saja.

| Endpoint                                        | Status              | Request                                             | Response                                                                                                                                                         |
| ------------------------------------------------ | -------------------- | ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `GET /api/kpi/approvals`                        | ✅ ada               | —                                                   | `{ data: User[] }` bawahan langsung dengan plan `SUBMITTED` (via `EvaluatorResolutionService`, slot PENILAI_1)                                                   |
| `GET /api/kpi/approvals/{userId}`               | ✅ ada               | —                                                   | `{ data: KpiPlan[] }` user tsb (semua item + bobot). 403 kalau caller bukan atasan pertamanya                                                                    |
| `POST /api/kpi/approvals/{userId}`              | ✅ ada               | `{ action: APPROVED\|REVISION_REQUESTED, comment }` | Insert baris baru `kpi_plan_reviews`; APPROVED → plan terkunci `APPROVED`, REVISION_REQUESTED → balik `DRAFT`. 403 kalau caller bukan atasan pertamanya          |
| `POST /api/kpi/approvals/{userId}/request-task` | ✅ ada (2026-07-16)  | `{ comment: required }`                             | Insert baris `kpi_plan_reviews` (`action=TASK_REQUESTED`, `kpi_plan_id=null`) — **tidak** mengubah status `kpi_plans`. 403 kalau caller bukan atasan pertamanya |

### 5.4 Sub-tab "Beri Penilaian" (+ Dispute — tampilan saja)

**Tampilan & data:**

- Paling atas: 2 kotak/tab **"Perlu Dinilai ({n})"** / **"Sudah Dinilai
  ({m})"** — pemisah list, bukan cuma badge warna.
- List nama di bawahnya. Nama yang sudah selesai dinilai tampil hijau +
  label "Telah dinilai" di bawah nama.
- Tap nama → detail: rencana kerja orang itu per item, plus
  **self-assessment**-nya (kosong kalau belum diisi). Evaluator isi
  **Nilai** + **Alasan** per item → tekan Simpan — **tersimpan otomatis per
  item** saat itu juga (bukan 1 tombol submit di akhir untuk semua item).
- **(2026-07-16)** Di bawah list item rencana kerja, tampilkan section
  **"Kriteria Penilaian Tambahan"** — muncul otomatis kalau HR sudah
  mengaktifkan minimal 1 kriteria di admin (`kpi_extra_criteria`), tanpa
  perlu konfigurasi tambahan apa pun di sisi mobile. Tiap kriteria tampil
  sebagai card: **nama** + **deskripsi**, lalu form **Nilai** + **Alasan** →
  Simpan (tersimpan otomatis per kriteria, sama seperti item rencana kerja).
  Skor ini ikut dijumlah ke skor akhir bulanan sebagai bucket tambahan di
  luar 5 bucket tetap (§7 `plan.md`) — kalau HR aktifkan 2 kriteria baru,
  total bucket yang dijumlah jadi 7.
- Tombol **Kembali** → balik ke list, bisa pilih nama lain.

Siapa menilai siapa mengikuti 3-penilai per level (lihat
`docs/kpi-calculation.md` §1): Staf dinilai Kasi/Kabag/rekan seksi; Kasi
dinilai Kabag/Direktur Bidang/rekan sesama Kasi; Kabag-setara dinilai
Direktur Bidang/Direktur Utama/rekan sesama Kabag.

**Dispute (masa sanggah, tampilan saja — §9 `plan.md`, dikonfirmasi &
direvisi 2026-07-16):** tidak ada tracker 4-centang per item. Selama masa
**Evaluation**, pegawai yang dinilai cuma lihat status ringkas per item
("2 dari 3 penilai sudah menilai") — **skor dan alasan tidak terbuka dulu**.
Baru begitu periode masuk fase **Dispute**, breakdown lengkap terbuka
**serentak** untuk semua pegawai sekaligus: skor + alasan dari tiap penilai
(termasuk komentar dari 2 atasan — Penilai 1 atasan langsung & Penilai 2
atasan-atasan/Direktur Bidang; Penilai 3 rekan tetap anonim). Ini disengaja
supaya tidak ada yang tahu skornya lebih dulu dari yang lain sebelum window
sanggah resmi dibuka. Konsisten dengan keputusan sanggah = dilihat saja di
app, keputusannya sendiri diselesaikan one-on-one dengan atasan di luar app —
jadi **tidak ada** tombol/form ajukan-sanggahan di app.

| Endpoint                              | Status | Request                                                                               | Response                                                                                                                                                                                                                                                                                                                            | Catatan                                                                                                                                                                                                                                                                                              |
| ------------------------------------- | ------ | ------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /api/kpi/evaluations/pending`    | ✅ ada | —                                                                                     | `{ data: [{ user_id, name, evaluator_role, is_peer, self_assessment_done, already_evaluated }] }` yang perlu dinilai user ini                                                                                                                                                                                                       | `evaluator_role` = PENILAI_1..3. `is_peer` dipakai Home §2.2 utk pisah counter rekan vs bawahan                                                                                                                                                                                                      |
| `GET /api/kpi/evaluations/{userId}`   | ✅ ada | —                                                                                     | `{ data: KpiPlan[], extra_criteria: [{id, name, description, score, reason}] }` (status APPROVED) user yang mau dinilai — `extra_criteria` (2026-07-16) = semua kriteria `is_active`, `score`/`reason` sudah terisi kalau evaluator ini sudah pernah menilai. 403 kalau caller bukan salah satu penilainya                                                                                                                                                                                                                         |                                                                                                                                                                                                                                                                                                      |
| `POST /api/kpi/evaluations/{userId}`  | ✅ ada | `{ kpi_plan_id, score, note }` — **1 item per call**, ikut alur simpan-per-item di UI | Insert/update 1 baris `kpi_evaluations` (unique per plan+evaluator, submit ulang = update)                                                                                                                                                                                                                                          | 403 kalau caller bukan penilainya; `evaluator_role` ditentukan server dari `EvaluatorResolutionService`, bukan dikirim client                                                                                                                                                                        |
| `POST /api/kpi/evaluations/{userId}/criteria` | ✅ ada (2026-07-16) | `{ kpi_extra_criterion_id, score, reason }` — 1 kriteria per call | Insert/update 1 baris `kpi_extra_criteria_scores` (unique per kriteria+user+period+evaluator) | Sama pola otorisasi dengan endpoint di atas |
| `GET /api/kpi/final-score?period_id=` | ✅ ada | query `period_id`                                                                     | Selama DRAFT/EVALUATION: `{ progress: [{kpi_plan_id, evaluators_done, evaluators_total}] }` saja (tanpa skor/alasan, `final_score`/`evaluations` null). Setelah `DISPUTE`/`CLOSED`: `{ final_score, evaluations: [{kpi_plan_id, evaluator_role, evaluator_name, score, note}], extra_criteria: [{id, name, score}] }` (Penilai 3 `evaluator_name` null, `progress` null) — `extra_criteria` (2026-07-16) breakdown skor kriteria tambahan, ikut aturan visibilitas yang sama | Server yang menentukan level detail berdasar `kpi_periods.status`, bukan client — supaya skor tidak bocor sebelum waktunya. Dipakai juga sbg tampilan masa sanggah — **tidak ada** endpoint submit dispute terpisah (`POST /api/kpi/disputes`/`GET /api/kpi/disputes/eligible` dihapus dari rencana) |

### 5.5 Sub-tab "Aduan Disiplin Pakaian & Integritas"

**Tampilan & data:** cari nama (dropdown searchable), foto (wajib untuk
Pakaian Dinas), deskripsi, pilih jenis pelanggaran (kalau Integritas → pilih
1 dari 8 sub-kategori). Integritas terkunci hanya atasan→bawahan; Pakaian
Dinas terbuka untuk semua pelapor.

| Endpoint                   | Status | Request                                                                                                                                                                    | Response                                             | Catatan                                                                                                            |
| -------------------------- | ------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `GET /api/users/search?q=` | ✅ ada | query `q`                                                                                                                                                                  | `{ data: [{ id, name, nik, department }] }` (max 20) | Autocomplete, versi non-admin dari pencarian pegawai                                                               |
| `POST /api/violations`     | ✅ ada | `{ reported_user_id, category: PAKAIAN_DINAS\|INTEGRITAS, description, integrity_category_id? (wajib jika INTEGRITAS), photo? (wajib jika PAKAIAN_DINAS), incident_date }` | `violation_reports` baru, status PENDING             | Server validasi atasan langsung→bawahan utk Integritas (403 kalau bukan). 422 kalau tidak ada periode KPI berjalan |

**Direksi:** tab KPI hanya menampilkan 5.4 (Direktur Bidang menilai
Kabag+Kasi; Dirut menilai Kabag-setara saja) dan 5.3 khusus Direktur Bidang
(approve rencana Kabag di direktoratnya). Sub-tab 5.1/5.2 disembunyikan
total untuk Direksi.

---

## 6. Profil

**Tampilan & data:** foto, nama, email, username. Skor KPI bulan lalu.
Tombol Logout.

**Logbook** (bagian dari Profil): list per tanggal (input dibatasi H-2
mundur), deskripsi + foto opsional, checkbox "terkait KPI" → kalau
dicentang, pilih salah satu target dari `kpi_plans` milik user sendiri.

**API:**

| Endpoint                        | Status | Request                                                                                                  | Response                                                                                                 |
| ------------------------------- | ------ | -------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| `POST /api/profile/photo`       | ✅ ada | `multipart/form-data: photo`                                                                             | `{ photo_url }` — mirror `Web\ProfileController::update` versi mobile                                    |
| `GET /api/logbook?month=&year=` | ✅ ada | query `month`, `year`                                                                                    | `{ data: DailyActivity[] }` milik user                                                                   |
| `POST /api/logbook`             | ✅ ada | `{ activity_date (H-2 s/d hari ini), description, photo?, kpi_plan_id? (jika "terkait KPI" dicentang) }` | Activity tersimpan (403 kalau `kpi_plan_id` bukan milik sendiri, 422 kalau tanggal di luar H-2/hari ini) |

---

## 7. Izin (Cuti / Sakit / Dinas Luar)

Tab besar baru, setara Absen/Live Location/KPI/Profil. Sub-tab bawah.
Bagian ini **masih dokumentasi saja** — panel admin webnya sudah dibangun,
tapi sisi mobile menyusul Fase D/E.

### 7.1 Sub-tab "Cuti"

**Tampilan & data:** form ajukan (tanggal mulai-selesai, alasan, tombol
Ajukan) + riwayat cuti sendiri (status PENDING/APPROVED/REJECTED, alasan
tolak kalau ada).

| Endpoint                   | Status | Request                            | Response                                           |
| -------------------------- | ------ | ---------------------------------- | -------------------------------------------------- |
| `GET /api/leave?type=CUTI` | ✅ ada | —                                  | `{ data: LeaveRequest[] }` riwayat cuti milik user |
| `POST /api/leave/cuti`     | ✅ ada | `{ start_date, end_date, reason }` | Row baru `status=PENDING`, `source=APP`            |

Approve/reject Cuti **tidak** ada di mobile — itu wewenang HR di panel
admin web saja.

### 7.2 Sub-tab "Sakit — Ajukan"

**Tampilan & data:** tombol "Sakit 1 Hari" (pilih hari ini/besok saja, tanpa
upload) dan tombol terpisah "Sakit >1 Hari" (rentang tanggal + wajib upload
surat dokter). **(2026-07-16, dipertegas)** Kedua mode **sama-sama wajib
approval atasan pertama** — 1 hari tanpa surat dokter tetap `status=PENDING`
sampai atasan approve, supaya tidak disalahgunakan; bukan auto-approve.

| Endpoint                    | Status | Request                                               | Response                                                                               | Catatan                                                                                                 |
| --------------------------- | ------ | ----------------------------------------------------- | -------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| `GET /api/leave?type=SAKIT` | ✅ ada | —                                                     | `{ data: LeaveRequest[] }` riwayat sakit milik user                                    |                                                                                                         |
| `POST /api/leave/sick`      | ✅ ada | `{ date }` **atau** `{ start_date, end_date, photo }` | Row baru `status=PENDING`, `source=APP` — kedua mode PENDING, menunggu approval atasan | Mode 1-hari: `date` harus hari ini/besok (422 kalau bukan), **tanpa** upload. Mode range: `photo` wajib |

### 7.3 Sub-tab "Sakit — Approval"

**Tampilan & data:** khusus atasan (muncul hanya kalau user punya bawahan).
List nama bawahan dengan pengajuan sakit PENDING → klik → lihat alasan +
foto surat dokter (kalau ada, hanya utk pengajuan >1 hari) → tombol
Approve/Tolak. Diakses juga lewat ikon lonceng di Home (§2.3).

| Endpoint                            | Status | Request                | Response                                                                    | Catatan                                                                                                            |
| ----------------------------------- | ------ | ---------------------- | --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `GET /api/leave/sick/pending`       | ✅ ada | —                      | `{ data: LeaveRequest[] }` bawahan langsung dengan sakit `status=PENDING`   | Kosong kalau user tidak punya bawahan. Panjang list ini = angka `pending_sick_approvals` di `GET /api/home` (§2.3) |
| `POST /api/leave/sick/{id}/approve` | ✅ ada | —                      | `status=APPROVED`, `approved_by_id`=user ini, trigger auto-inject kehadiran | 403 kalau caller bukan atasan pertama pemilik request                                                              |
| `POST /api/leave/sick/{id}/reject`  | ✅ ada | `{ rejection_reason }` | `status=REJECTED`                                                           | idem otorisasi                                                                                                     |

### 7.4 Sub-tab "Dinas Luar"

**Tampilan & data:** **read-only** — riwayat DL yang HR sudah assign ke
pegawai ini. Tidak ada form input (input DL cuma di web HR, karena dasarnya
SPPD fisik yang di-input HR untuk banyak pegawai sekaligus).

| Endpoint                         | Status | Request | Response                                         |
| -------------------------------- | ------ | ------- | ------------------------------------------------ |
| `GET /api/leave?type=DINAS_LUAR` | ✅ ada | —       | `{ data: LeaveRequest[] }` riwayat DL milik user |

Tidak ada endpoint submit DL dari sisi pegawai.

---

## 8. Kebutuhan Backend Tambahan (bukan endpoint, prasyarat sebelum API di atas bisa jalan)

1. ✅ `EvaluatorResolutionService` (`app/Services/EvaluatorResolutionService.php`, 2026-07-16) — dipakai §5.3/§5.4 (approvals, evaluations) dan §4 (live-location visibility pejabat). Baca bobot dari `kpi_evaluator_weights`.
2. ✅ `LocationVisibilityService` (`app/Services/LocationVisibilityService.php`) — §4, termasuk cakupan "subordinates" Kasi yang diperluas ke 1 Bagian dan `level_filter` khusus Direksi (§4.1).
3. ✅ Migrasi: `kpi_plans.status` — `SUBMITTED` (kolom `string` polos, tidak perlu migrasi enum DB, tinggal dipakai).
4. ✅ Migrasi: tabel `kpi_plan_reviews`, termasuk kolom `kpi_plan_id` (nullable) langsung dari awal.
5. ✅ Migrasi + seeder: `kpi_evaluator_weights` (33/33/34 seragam Staf/Kasi/Kabag-setara).
6. ⬜ Channel Reverb privat untuk broadcast live location — baru ada default `App.Models.User.{id}`. Tanpa ini, §4 harus polling (belum jadi blocker, endpoint `GET /api/location/colleagues` sudah bisa dipolling).
7. ✅ `KpiEvaluationService` (`app/Services/KpiEvaluationService.php`) — trigger otomatis saat `KpiPeriodsTable::updateStatus` transisi ke `CLOSED` (web admin). Juga sumber data `GET /api/kpi/final-score`.
8. ✅ Approval kehadiran (telat/cepat/luar geofence, §3) — dibangun di web admin (`Livewire\Admin\AttendancesTable::approve/reject`), bukan endpoint mobile.
9. ⬜ Push notifikasi OS (FCM/APNs) untuk reminder penilaian (§2.2) dan bell sakit (§2.3) — belum diasumsikan tersedia, lihat catatan di §2. `pending_evaluations`/`pending_sick_approvals` di `GET /api/home` sudah tersedia sbg data sumber badge in-app.
10. ⬜ Migrasi tambahan yang ditemukan saat implementasi (belum di draf awal dokumen ini): `violation_reports.integrity_category_id` (FK ke `kpi_integrity_categories`, wajib utk hitung skor Integritas per kategori) dan `kpi_evaluations.note` (kolom "Alasan" yang didokumentasikan di §5.4 tapi belum ada di migration lama) — **kedua migrasi ini sudah dibuat (2026-07-16)**.
11. ✅ Migrasi (2026-07-16): tabel `kpi_extra_criteria` (master, admin `/admin/kpi-categories`), `kpi_extra_criteria_scores` (evaluator × kriteria × periode), `kpi_final_score_extras` (breakdown per kriteria di `kpi_final_scores`) — lihat §5.4. `KpiEvaluationService::calculateForUser()` diperluas untuk menjumlahkan skor kriteria aktif ke `grand_total_score`, di luar 5 bucket tetap.
12. ✅ `kpi_plan_reviews.action` (2026-07-16): nilai baru `TASK_REQUESTED` (kolom sudah `string` polos, tidak perlu migrasi) — dipakai `POST /api/kpi/approvals/{userId}/request-task`, lihat §5.3.
