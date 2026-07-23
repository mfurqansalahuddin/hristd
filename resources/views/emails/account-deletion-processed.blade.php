<!doctype html>
<html>
<body style="font-family: sans-serif; color: #1f2937;">
    <p>Halo {{ $deletionRequest->name }},</p>

    @if ($deletionRequest->status === 'APPROVED')
        <p>
            Permintaan penghapusan akun HRIS TD Anda telah <strong>disetujui dan diproses</strong>.
            Akun beserta seluruh data terkait (absensi, logbook, izin, dan KPI) telah dihapus dari sistem kami.
        </p>
    @else
        <p>
            Permintaan penghapusan akun HRIS TD Anda <strong>ditolak</strong> dengan alasan berikut:
        </p>
        <p style="padding: 12px; background: #f3f4f6; border-radius: 8px;">{{ $deletionRequest->rejection_reason }}</p>
        <p>Jika Anda merasa ini adalah kekeliruan, silakan hubungi HR Perumdam Tirta Daroy.</p>
    @endif

    <p>Terima kasih,<br>HRIS TD &mdash; Perumdam Tirta Daroy</p>
</body>
</html>
