<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\URL::forceScheme('https'); // Force for now to be sure

        \Illuminate\Support\Facades\View::composer('layouts.partials.topbar', function ($view) {
            $notifications = collect();
            $today = \Illuminate\Support\Carbon::now('Asia/Makassar')->toDateString();

            // 1. Pending Users Registration
            $pendingUsers = \App\Models\User::where('role', 'user')->where('status', '!=', 'verified')->get();
            foreach($pendingUsers as $u) {
                $notifications->push([
                    'id' => 'u'.$u->id,
                    'icon' => 'fas fa-user-plus',
                    'color' => 'primary',
                    'time_raw' => $u->created_at,
                    'time' => \Illuminate\Support\Carbon::parse($u->created_at)->diffForHumans(),
                    'text' => 'Pendaftaran akun baru: ' . $u->nama,
                    'link' => route('verifikasi.user')
                ]);
            }

            // 2. Absensi Hari Ini
            $absensi = \App\Models\Attendance::with('user')->where('tanggal', $today)->get();
            foreach($absensi as $a) {
                $tgl = $a->tanggal;
                
                if ($a->jam_masuk) {
                    $time_masuk = $a->ceklog_masuk ? \Illuminate\Support\Carbon::parse($a->ceklog_masuk) : \Illuminate\Support\Carbon::parse($tgl . ' ' . $a->jam_masuk);
                    $notifications->push([
                        'id' => 'am'.$a->id,
                        'icon' => 'fas fa-sign-in-alt',
                        'color' => 'info',
                        'time_raw' => $time_masuk,
                        'time' => $time_masuk->diffForHumans(),
                        'text' => ($a->user->nama ?? 'User') . ' melakukan absen masuk',
                        'link' => route('admin.dashboard', ['start_date' => $today, 'end_date' => $today])
                    ]);
                }

                if ($a->jam_pulang) {
                    $time_pulang = $a->ceklog_pulang ? \Illuminate\Support\Carbon::parse($a->ceklog_pulang) : \Illuminate\Support\Carbon::parse($tgl . ' ' . $a->jam_pulang);
                    $notifications->push([
                        'id' => 'ap'.$a->id,
                        'icon' => 'fas fa-sign-out-alt',
                        'color' => 'success',
                        'time_raw' => $time_pulang,
                        'time' => $time_pulang->diffForHumans(),
                        'text' => ($a->user->nama ?? 'User') . ' melakukan absen pulang',
                        'link' => route('admin.dashboard', ['start_date' => $today, 'end_date' => $today])
                    ]);
                }
            }

            // 3. Laporan Kejadian Hari Ini
            $laporan = \App\Models\IncidentReport::with('user')->where('tanggal', $today)->get();
            foreach($laporan as $l) {
                $time_lap = \Illuminate\Support\Carbon::parse($l->tanggal . ' ' . ($l->jam ?? '00:00:00'));
                
                $notifications->push([
                    'id' => 'l'.$l->id,
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'warning',
                    'time_raw' => $time_lap,
                    'time' => $time_lap->diffForHumans(),
                    'text' => 'Laporan kejadian dari ' . ($l->user->nama ?? 'User'),
                    'link' => route('admin.laporan', ['start_date' => $today, 'end_date' => $today])
                ]);
            }

            // Urutkan berdasarkan waktu terbaru (descending) dan ambil top 5
            $sortedNotifications = $notifications->sortByDesc('time_raw')->take(5);

            $user = \Illuminate\Support\Facades\Auth::user();
            $lastRead = $user && $user->last_read_notifications ? \Illuminate\Support\Carbon::parse($user->last_read_notifications) : \Illuminate\Support\Carbon::create(2000, 1, 1);
            $unreadCount = $notifications->filter(function($item) use ($lastRead) {
                return $item['time_raw']->gt($lastRead);
            })->count();

            $view->with('notifications', $sortedNotifications);
            $view->with('notificationsCount', $unreadCount);
        });
    }
}
