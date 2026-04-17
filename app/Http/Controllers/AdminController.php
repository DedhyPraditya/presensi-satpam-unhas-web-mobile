<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\IncidentReport;
use App\Models\AppSetting;
use App\Models\PosLokasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    // ─── Helper: get or create config ───────────────────────────────────────
    private function getConfig(): AppSetting
    {
        return AppSetting::firstOrCreate(['id' => 1], [
            'jam_masuk_non_shift_pagi'  => '07:30:00',
            'jam_pulang_non_shift_pagi' => '17:00:00',
            'jam_masuk_shift_pagi'      => '07:00:00',
            'jam_pulang_shift_pagi'     => '19:00:00',
            'jam_masuk_shift_malam'     => '19:00:00',
            'jam_pulang_shift_malam'    => '07:00:00',
        ]);
    }

    // ─── ADMIN DASHBOARD ────────────────────────────────────────────────────
    public function dashboard(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->toDateString());
        $endDate   = $request->query('end_date',   Carbon::now()->toDateString());

        if (! strtotime($startDate)) $startDate = Carbon::now()->startOfMonth()->toDateString();
        if (! strtotime($endDate))   $endDate   = Carbon::now()->endOfMonth()->toDateString();

        $config = $this->getConfig();
        $shifts = [
            'non_shift_pagi' => ['masuk' => $config->jam_masuk_non_shift_pagi, 'pulang' => $config->jam_pulang_non_shift_pagi],
            'shift_pagi'     => ['masuk' => $config->jam_masuk_shift_pagi,     'pulang' => $config->jam_pulang_shift_pagi],
            'shift_malam'    => ['masuk' => $config->jam_masuk_shift_malam,    'pulang' => $config->jam_pulang_shift_malam],
        ];

        $today        = Carbon::now()->toDateString();
        $totalSatpam  = User::where('role', 'user')->where('status', 'verified')->count();
        $pendingCount = User::where('role', 'user')->where('status', '!=', 'verified')->count();

        $todayAttendances = Attendance::where('tanggal', $today)->whereNotNull('jam_masuk')->get();
        $hadirToday       = $todayAttendances->count();
        $terlambatToday   = $todayAttendances->where('terlambat', 'Ya')->count();
        $belumHadir       = max(0, $totalSatpam - $hadirToday);

        $laporanHariIni = IncidentReport::where('tanggal', $today)->count();

        // Build period
        $period  = [];
        $current = Carbon::parse($startDate);
        $end     = Carbon::parse($endDate);
        while ($current->lessThanOrEqualTo($end)) {
            $period[] = $current->toDateString();
            $current->addDay();
        }

        $posLocations = PosLokasi::all();
        $users = User::with(['absensi' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal', [$startDate, $endDate]);
        }, 'posLokasi'])
            ->where('role', 'user')
            ->orderBy('nama')
            ->get();

        return view('admin.dashboard', [
            'title' => 'Ringkasan Dashboard',
            'totalSatpam' => $totalSatpam,
            'hadirToday' => $hadirToday,
            'terlambatToday' => $terlambatToday,
            'belumHadir' => $belumHadir,
            'pendingCount' => $pendingCount,
            'laporanHariIni' => $laporanHariIni,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'posLocations' => $posLocations,
            'users' => $users,
            'period' => $period,
            'shifts' => $shifts
        ]);
    }

    // ─── VERIFIKASI USER ─────────────────────────────────────────────────────
    public function verifikasiUser()
    {
        $pendingUsers = User::where('role', 'user')
            ->where('status', '!=', 'verified')
            ->with('posLokasi')
            ->orderBy('created_at', 'desc')
            ->get();

        $verifiedUsers = User::where('role', 'user')
            ->where('status', 'verified')
            ->with('posLokasi')
            ->orderBy('nama')
            ->get();

        $positions = PosLokasi::orderBy('nama_pos')->get();

        return view('admin.verifikasi_user', [
            'title' => 'Verifikasi User',
            'pendingUsers' => $pendingUsers,
            'verifiedUsers' => $verifiedUsers,
            'positions' => $positions
        ]);
    }

    public function verifyUser(User $user)
    {
        $user->update(['status' => 'verified']);
        return redirect()->route('verifikasi.user')->with('success', 'User ' . $user->nama . ' berhasil diverifikasi.');
    }

    public function rejectUser(User $user)
    {
        $user->delete();
        return redirect()->route('verifikasi.user')->with('success', 'Akun berhasil ditolak dan dihapus.');
    }

    public function unverifyUser(User $user)
    {
        $user->update(['status' => 'pending']);
        return redirect()->route('verifikasi.user')->with('success', 'Verifikasi akun ' . $user->nama . ' berhasil dibatalkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'nama'        => 'required|string|max:255',
            'nip'         => 'nullable|string|max:50',
            'id_pos'      => 'nullable|exists:pos_lokasi,id',
            'jenis_kerja' => 'required|in:shift,non_shift',
        ]);

        $user->update($request->only(['nama', 'nip', 'id_pos', 'jenis_kerja']));
        return redirect()->route('verifikasi.user')->with('success', 'Data akun ' . $user->nama . ' berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('verifikasi.user')->with('success', 'Akun berhasil dihapus secara permanen.');
    }

    // ─── POS JAGA ────────────────────────────────────────────────────────────
    public function editPosJaga(PosLokasi $pos)
    {
        return view('admin.edit_pos_jaga', [
            'title' => 'Edit Pos Jaga',
            'position' => $pos
        ]);
    }

    public function updatePosJaga(Request $request, PosLokasi $pos)
    {
        $request->validate([
            'nama_pos'  => 'required|string|max:255',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius'    => 'required|integer|min:10',
        ]);
        $pos->update($request->only(['nama_pos', 'latitude', 'longitude', 'radius']));
        return redirect()->route('tambah.pos')->with('success', 'Pos jaga berhasil diperbarui.');
    }

    public function destroyPosJaga(PosLokasi $pos)
    {
        $pos->delete();
        return redirect()->route('tambah.pos')->with('success', 'Pos jaga berhasil dihapus.');
    }

    public function tambahPosJaga(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'nama_pos'  => 'required|string|max:255',
                'latitude'  => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius'    => 'required|integer|min:10',
            ]);
            PosLokasi::create($request->only(['nama_pos', 'latitude', 'longitude', 'radius']));
            return redirect()->route('tambah.pos')->with('success', 'Pos jaga baru berhasil ditambahkan.');
        }
        $positions = PosLokasi::orderBy('nama_pos')->get();
        return view('admin.tambah_pos_jaga', [
            'title' => 'Manajemen Pos Jaga',
            'positions' => $positions
        ]);
    }

    // ─── JAM KERJA ───────────────────────────────────────────────────────────
    public function editJamKerja(Request $request)
    {
        $config = $this->getConfig();

        if ($request->isMethod('post')) {
            $request->validate([
                'jam_masuk_non_shift_pagi'  => 'required|date_format:H:i',
                'jam_pulang_non_shift_pagi' => 'required|date_format:H:i',
                'jam_masuk_shift_pagi'      => 'required|date_format:H:i',
                'jam_pulang_shift_pagi'     => 'required|date_format:H:i',
                'jam_masuk_shift_malam'     => 'required|date_format:H:i',
                'jam_pulang_shift_malam'    => 'required|date_format:H:i',
            ]);
            $config->update([
                'jam_masuk_non_shift_pagi'  => $request->input('jam_masuk_non_shift_pagi')  . ':00',
                'jam_pulang_non_shift_pagi' => $request->input('jam_pulang_non_shift_pagi') . ':00',
                'jam_masuk_shift_pagi'      => $request->input('jam_masuk_shift_pagi')      . ':00',
                'jam_pulang_shift_pagi'     => $request->input('jam_pulang_shift_pagi')     . ':00',
                'jam_masuk_shift_malam'     => $request->input('jam_masuk_shift_malam')     . ':00',
                'jam_pulang_shift_malam'    => $request->input('jam_pulang_shift_malam')    . ':00',
            ]);
            return redirect()->route('edit.jam')->with('success', 'Jam kerja berhasil diperbarui.');
        }

        return view('admin.edit_jam_kerja', [
            'title' => 'Pengaturan Jam Kerja',
            'config' => $config
        ]);
    }

    // ─── RINGKASAN SISTEM ────────────────────────────────────────────────────
    public function ringkasanSistem()
    {
        $config          = $this->getConfig();
        $totalUsers      = User::where('role', 'user')->count();
        $totalPos        = PosLokasi::count();
        $today           = Carbon::now()->toDateString();
        $attendancesToday = Attendance::where('tanggal', $today)->count();
        $lateToday       = Attendance::where('tanggal', $today)->where('terlambat', 'Ya')->count();
        $onTimeToday     = max(0, $attendancesToday - $lateToday);

        return view('admin.ringkasan_sistem', [
            'title' => 'Ringkasan Sistem',
            'config' => $config,
            'totalUsers' => $totalUsers,
            'totalPos' => $totalPos,
            'attendancesToday' => $attendancesToday,
            'lateToday' => $lateToday,
            'onTimeToday' => $onTimeToday
        ]);
    }

    // ─── EKSPOR LAPORAN ───────────────────────────────────────────────────────
    public function eksporLaporan(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date',   Carbon::now()->endOfMonth()->toDateString());
        $userId    = $request->query('user_id',    'all');
        $posId     = $request->query('pos_id',     'all');

        $query = Attendance::with(['user', 'user.posLokasi'])
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }
        
        if ($posId !== 'all') {
            $query->whereHas('user', function($q) use ($posId) {
                $q->where('id_pos', $posId);
            });
        }

        $attendances = $query->orderBy('tanggal')->get();
        $users       = User::where('role', 'user')->orderBy('nama')->get();
        $positions   = PosLokasi::orderBy('nama_pos')->get();

        if ($request->query('download') === 'csv') {
            return $this->downloadAttendance($attendances, $startDate, $endDate);
        }

        return view('admin.ekspor_laporan', [
            'title' => 'Ekspor Laporan Absensi',
            'attendances' => $attendances,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'users' => $users,
            'positions' => $positions,
            'userId' => $userId,
            'posId' => $posId
        ]);
    }

    protected function downloadAttendance($attendances, string $startDate, string $endDate)
    {
        $fileName = sprintf('laporan_absensi_%s_%s.csv', $startDate, $endDate);
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'NIP', 'Nama', 'Jenis Kerja', 'POS Jaga', 'Jam Masuk', 'Jam Pulang', 'Terlambat', 'Cepat Pulang', 'Latitude', 'Longitude']);
            foreach ($attendances as $a) {
                fputcsv($handle, [
                    $a->tanggal instanceof \Carbon\Carbon ? $a->tanggal->format('Y-m-d') : $a->tanggal, 
                    $a->user?->nip, 
                    $a->user?->nama,
                    $a->jenis_kerja, 
                    $a->user?->posLokasi?->nama_pos,
                    $a->jam_masuk, 
                    $a->jam_pulang,
                    $a->terlambat, 
                    $a->cepat_pulang,
                    $a->latitude, 
                    $a->longitude,
                ]);
            }
            fclose($handle);
        };
        return response()->streamDownload($callback, $fileName, $headers);
    }

    // ─── LAPORAN KEJADIAN ────────────────────────────────────────────────────
    public function laporanKejadian(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date',   Carbon::now()->endOfMonth()->toDateString());
        $userId    = $request->query('user_id');

        $query = IncidentReport::with('user')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($userId && $userId !== 'all') {
            $query->where('user_id', $userId);
        }

        $laporan = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        $users = User::where('role', 'user')->where('status', 'verified')->orderBy('nama')->get();

        return view('admin.laporan_kejadian', [
            'title' => 'Laporan Kejadian Personel',
            'laporan' => $laporan,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userId' => $userId,
            'users' => $users
        ]);
    }

    public function printLaporan(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date',   Carbon::now()->endOfMonth()->toDateString());
        $userId    = $request->query('user_id');

        $query = IncidentReport::with(['user', 'user.posLokasi'])
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($userId && $userId !== 'all') {
            $query->where('user_id', $userId);
        }

        $laporan = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        $selectedUser = null;
        if ($userId && $userId !== 'all') {
            $selectedUser = User::find($userId);
        }

        return view('admin.print_laporan', [
            'title' => 'Cetak Laporan Kejadian',
            'laporan' => $laporan,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userId' => $userId,
            'selectedUser' => $selectedUser
        ]);
    }

    public function deleteLaporan(IncidentReport $l)
    {
        $fotoPath = public_path('storage/' . $l->foto); // Modern Laravel uses storage
        if ($l->foto && file_exists($fotoPath)) {
            @unlink($fotoPath);
        }
        $l->delete();
        return redirect()->route('admin.laporan')->with('success', 'Laporan berhasil dihapus.');
    }

    public function markNotificationsRead()
    {
        if ($user = Auth::user()) {
            $user->last_read_notifications = now();
            $user->save();
        }
        return response()->json(['success' => true]);
    }

    public function destroyAbsensi(Attendance $attendance)
    {
        // Hapus foto jika ada
        if ($attendance->foto_masuk) {
            @unlink(storage_path('app/public/' . $attendance->foto_masuk));
        }
        if ($attendance->foto_pulang) {
            @unlink(storage_path('app/public/' . $attendance->foto_pulang));
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil di-reset.');
    }
}
