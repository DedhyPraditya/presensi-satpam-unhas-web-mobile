@extends('layouts.sb-admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pengaturan Jam Kerja</h1>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <form action="{{ route('edit.jam.save') }}" method="POST" class="card shadow border-0 overflow-hidden">
            @csrf
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clock mr-2"></i>Konfigurasi Jadwal & Shift</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Non-Shift -->
                    <div class="col-md-4 mb-4">
                        <div class="p-3 bg-light rounded shadow-sm border-left-primary">
                            <h6 class="font-weight-bold text-primary mb-3">Non-Shift (Reguler)</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Jam Masuk</label>
                                <input type="time" name="jam_masuk_non_shift_pagi" value="{{ substr($config->jam_masuk_non_shift_pagi, 0, 5) }}" class="form-control shadow-sm" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Jam Pulang</label>
                                <input type="time" name="jam_pulang_non_shift_pagi" value="{{ substr($config->jam_pulang_non_shift_pagi, 0, 5) }}" class="form-control shadow-sm" required>
                            </div>
                            <small class="text-muted">Digunakan untuk personel tim administrasi/reguler.</small>
                        </div>
                    </div>

                    <!-- Shift Pagi -->
                    <div class="col-md-4 mb-4">
                        <div class="p-3 bg-light rounded shadow-sm border-left-success">
                            <h6 class="font-weight-bold text-success mb-3">Shift Pagi (Layanan)</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Jam Masuk</label>
                                <input type="time" name="jam_masuk_shift_pagi" value="{{ substr($config->jam_masuk_shift_pagi, 0, 5) }}" class="form-control shadow-sm" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Jam Pulang</label>
                                <input type="time" name="jam_pulang_shift_pagi" value="{{ substr($config->jam_pulang_shift_pagi, 0, 5) }}" class="form-control shadow-sm" required>
                            </div>
                            <small class="text-muted">Jadwal jaga personel operasional di pagi hari.</small>
                        </div>
                    </div>

                    <!-- Shift Malam -->
                    <div class="col-md-4 mb-4">
                        <div class="p-3 bg-light rounded shadow-sm border-left-warning">
                            <h6 class="font-weight-bold text-warning mb-3">Shift Malam (Layanan)</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Jam Masuk</label>
                                <input type="time" name="jam_masuk_shift_malam" value="{{ substr($config->jam_masuk_shift_malam, 0, 5) }}" class="form-control shadow-sm" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Jam Pulang</label>
                                <input type="time" name="jam_pulang_shift_malam" value="{{ substr($config->jam_pulang_shift_malam, 0, 5) }}" class="form-control shadow-sm" required>
                            </div>
                            <small class="text-muted">Jadwal jaga personel operasional di malam hari.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 py-4 text-center">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 font-weight-bold shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
