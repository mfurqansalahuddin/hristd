<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * §14.2/§14.3, §15.2 mobile-app.md — clock-in/out geofencing + riwayat absen.
 */
class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'long' => ['required', 'numeric'],
            'approval_reason' => ['nullable', 'string'],
            'mocked' => ['nullable', 'boolean'],
        ]);

        if ($data['mocked'] ?? false) {
            throw ValidationException::withMessages(['mocked' => ['Terdeteksi lokasi GPS palsu, absen ditolak.']]);
        }

        $user = $request->user();
        $today = now()->toDateString();

        if (Attendance::where('user_id', $user->id)->whereDate('date', $today)->whereNotNull('clock_in')->exists()) {
            throw ValidationException::withMessages(['clock_in' => ['Sudah absen masuk hari ini.']]);
        }

        $locations = Location::all();
        $matchedLocation = $locations->first(fn (Location $location) => $location->containsPoint($data['lat'], $data['long']));

        $isLate = now()->format('H:i:s') > Attendance::JAM_MASUK_BATAS;
        $needsApproval = $isLate || ! $matchedLocation;

        if ($needsApproval && empty($data['approval_reason'])) {
            throw ValidationException::withMessages(['approval_reason' => ['Wajib diisi kalau telat atau di luar lokasi kantor.']]);
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'clock_in' => now(),
                'clock_in_lat' => $data['lat'],
                'clock_in_long' => $data['long'],
                'status' => $isLate ? 'TELAT' : 'HADIR',
                'is_apel' => now()->isMonday() && ! $isLate,
                'approval_reason' => $data['approval_reason'] ?? null,
                'supervisor_approval' => $needsApproval ? 'PENDING' : 'APPROVED',
            ]
        );

        return response()->json([
            'attendance' => $attendance,
            'matched_location' => $matchedLocation?->name,
        ]);
    }

    public function clockOut(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'long' => ['required', 'numeric'],
            'approval_reason' => ['nullable', 'string'],
            'mocked' => ['nullable', 'boolean'],
        ]);

        if ($data['mocked'] ?? false) {
            throw ValidationException::withMessages(['mocked' => ['Terdeteksi lokasi GPS palsu, absen ditolak.']]);
        }

        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->whereNotNull('clock_in')->first();

        if (! $attendance) {
            throw ValidationException::withMessages(['clock_in' => ['Belum absen masuk hari ini.']]);
        }

        if ($attendance->clock_out) {
            throw ValidationException::withMessages(['clock_out' => ['Sudah absen keluar hari ini.']]);
        }

        $locations = Location::all();
        $matchedLocation = $locations->first(fn (Location $location) => $location->containsPoint($data['lat'], $data['long']));

        $closeTime = now()->isSaturday() ? Attendance::JAM_PULANG_BATAS_SABTU : Attendance::JAM_PULANG_BATAS;
        $isEarly = now()->format('H:i:s') < $closeTime;
        $needsApproval = $isEarly || ! $matchedLocation;

        if ($needsApproval && empty($data['approval_reason']) && ! $attendance->approval_reason) {
            throw ValidationException::withMessages(['approval_reason' => ['Wajib diisi kalau pulang cepat atau di luar lokasi kantor.']]);
        }

        $attendance->update([
            'clock_out' => now(),
            'clock_out_lat' => $data['lat'],
            'clock_out_long' => $data['long'],
            'status' => $attendance->status === 'TELAT' ? 'TELAT' : ($isEarly ? 'PULANG_CEPAT' : $attendance->status),
            'approval_reason' => $data['approval_reason'] ?? $attendance->approval_reason,
            'supervisor_approval' => $needsApproval ? 'PENDING' : $attendance->supervisor_approval,
        ]);

        return response()->json([
            'attendance' => $attendance->fresh(),
            'matched_location' => $matchedLocation?->name,
        ]);
    }

    public function history(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $locations = Location::all();

        $attendances = Attendance::where('user_id', $request->user()->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderByDesc('date')
            ->get()
            ->map(fn (Attendance $attendance) => [
                'id' => $attendance->id,
                'date' => $attendance->date->toDateString(),
                'clock_in' => $attendance->clock_in?->format('H:i'),
                'clock_out' => $attendance->clock_out?->format('H:i'),
                'status_masuk' => $attendance->statusMasuk(),
                'status_pulang' => $attendance->statusPulang(),
                'matched_location_in' => $attendance->matchedLocation('clock_in', $locations)?->name,
                'matched_location_out' => $attendance->matchedLocation('clock_out', $locations)?->name,
                'status' => $attendance->status,
                'supervisor_approval' => $attendance->supervisor_approval,
            ]);

        return response()->json(['data' => $attendances]);
    }
}
