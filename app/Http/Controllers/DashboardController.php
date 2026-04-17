<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\IncidentReport;
use App\Models\PosLokasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        
        $stats = [
            'total_satpam' => User::where('role', 'user')->where('status', 'verified')->count(),
            'present_today' => Attendance::where('tanggal', $today)->count(),
            'late_today' => Attendance::where('tanggal', $today)->where('terlambat', 'Ya')->count(),
            'incident_reports' => IncidentReport::count(),
        ];

        $recent_logs = Attendance::with('user')->orderBy('created_at', 'desc')->limit(10)->get();
        $pos_points = PosLokasi::all();

        return view('admin.dashboard', [
            'title' => 'Ringkasan Dashboard',
            'stats' => $stats,
            'recent_logs' => $recent_logs,
            'pos_points' => $pos_points
        ]);
    }
}
