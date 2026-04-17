@extends('layouts.sb-admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ringkasan Sistem</h1>
</div>

<div class="row">
    <!-- Configuration Card -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cogs mr-2"></i>Konfigurasi Jam Kerja Saat Ini</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm border-0">
                        <tr class="bg-light"><td colspan="2" class="font-weight-bold py-2 px-3 text-primary small">SISTEM NON-SHIFT</td></tr>
                        <tr><td class="px-3 py-2 small">Masuk: <b>{{ $config->jam_masuk_non_shift_pagi }}</b></td><td class="px-3 py-2 small text-right">Pulang: <b>{{ $config->jam_pulang_non_shift_pagi }}</b></td></tr>
                        
                        <tr class="bg-light"><td colspan="2" class="font-weight-bold py-2 px-3 text-success small">SISTEM SHIFT</td></tr>
                        <tr><td class="px-3 py-2 small">Shift Pagi: <b>{{ $config->jam_masuk_shift_pagi }} - {{ $config->jam_pulang_shift_pagi }}</b></td><td class="px-3 py-2 small text-right">Durasi: 12 Jam</td></tr>
                        <tr><td class="px-3 py-2 small">Shift Malam: <b>{{ $config->jam_masuk_shift_malam }} - {{ $config->jam_pulang_shift_malam }}</b></td><td class="px-3 py-2 small text-right">Durasi: 12 Jam</td></tr>
                    </table>
                </div>
                <div class="mt-3 text-center">
                    <a href="{{ route('edit.jam') }}" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>Ubah Konfigurasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Matrix Card -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i>Matriks Operasional Hari Ini</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-4">
                        <div class="p-3 bg-light rounded shadow-sm">
                            <h2 class="font-weight-bold text-gray-800 mb-0">{{ $totalUsers }}</h2>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size:10px;">Total Personel</small>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="p-3 bg-light rounded shadow-sm">
                            <h2 class="font-weight-bold text-gray-800 mb-0">{{ $totalPos }}</h2>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size:10px;">Titik POS Jaga</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2">
                            <h4 class="font-weight-bold text-primary mb-0">{{ $attendancesToday }}</h4>
                            <small class="text-muted" style="font-size:9px;">TOTAL ABSEN</small>
                        </div>
                    </div>
                    <div class="col-4" style="border-left: 1px solid #eee; border-right: 1px solid #eee;">
                        <div class="p-2">
                            <h4 class="font-weight-bold text-success mb-0">{{ $onTimeToday }}</h4>
                            <small class="text-muted" style="font-size:9px;">TEPAT WAKTU</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2">
                            <h4 class="font-weight-bold text-danger mb-0">{{ $lateToday }}</h4>
                            <small class="text-muted" style="font-size:9px;">TERLAMBAT</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted text-center pt-2">
                    <i class="fas fa-info-circle mr-1"></i> Data di atas diambil berdasarkan statistik kehadiran real-time per hari ini.
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
