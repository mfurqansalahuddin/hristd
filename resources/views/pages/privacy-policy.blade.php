@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 min-h-screen bg-white px-6 py-10 dark:bg-gray-900">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-8">
            <x-errors.back-button />

            <div class="flex flex-col items-center text-center">
                <img src="{{ asset('Logo%20TD%20nobg.png') }}" alt="Logo Perumdam Tirta Daroy" class="mb-4 h-16 w-auto">
                <h1 class="text-title-sm sm:text-title-md font-semibold text-gray-800 dark:text-white/90">
                    Kebijakan Privasi
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">HRIS TD &mdash; Perumdam Tirta Daroy</p>
            </div>

            <div class="flex flex-col gap-6 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">Data yang Kami Kumpulkan</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Identitas: nama, NIK, username, email, foto profil.</li>
                        <li>Lokasi GPS &mdash; aktif selama aplikasi terbuka (bukan hanya saat proses absen), untuk memverifikasi kehadiran di lokasi kerja.</li>
                        <li>Data kepegawaian: absensi, logbook harian, pengajuan izin/cuti/dinas luar, dan penilaian KPI.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">Tujuan Penggunaan Data</h2>
                    <p>
                        Data di atas digunakan untuk memverifikasi kehadiran pegawai, mencatat riwayat kerja, memproses
                        pengajuan izin/cuti, dan penilaian kinerja (KPI) oleh atasan langsung.
                    </p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">Berbagi Data dengan Pengguna Lain</h2>
                    <p>
                        Lokasi Anda saat ini dapat dilihat oleh atasan langsung dan rekan kerja sesuai level jabatan
                        (kepala bagian, kepala seksi, direksi) sebagai bagian dari fitur pemantauan kehadiran tim. Data
                        ini tidak dibagikan ke pihak di luar Perumdam Tirta Daroy.
                    </p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">Keamanan Data</h2>
                    <p>
                        Sesi masuk (token) dan kata sandi yang tersimpan di perangkat mobile dienkripsi menggunakan
                        penyimpanan aman bawaan sistem operasi (Android Keystore).
                    </p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">Retensi &amp; Penghapusan Data</h2>
                    <p>
                        Data disimpan selama Anda berstatus pegawai aktif. Akun dibuat dan dikelola oleh HR, sehingga
                        permintaan penghapusan akun dan seluruh data terkait diajukan melalui formulir berikut, lalu
                        diproses oleh HR setelah verifikasi identitas:
                    </p>
                    <p class="mt-2">
                        <a href="{{ route('account-deletion.create') }}" class="font-medium text-brand-500 underline">
                            Ajukan Penghapusan Akun
                        </a>
                    </p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">Kontak</h2>
                    <p>Pertanyaan seputar kebijakan privasi ini dapat disampaikan ke bagian HR Perumdam Tirta Daroy.</p>
                </section>
            </div>

            <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                &copy; {{ date('Y') }} Perumdam Tirta Daroy
            </p>
        </div>
    </div>
@endsection
