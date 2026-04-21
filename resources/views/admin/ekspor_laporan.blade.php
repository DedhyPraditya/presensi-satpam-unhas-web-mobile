@extends('layouts.sb-admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ekspor Laporan Absensi</h1>
</div>

<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Filter Data Ekspor</h6>
    </div>
    <div class="card-body">
        <form method="get" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-dark"><i class="fas fa-calendar-alt mr-1"></i>Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm shadow-sm">
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-dark"><i class="fas fa-calendar-alt mr-1"></i>Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm shadow-sm">
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-dark"><i class="fas fa-user-shield mr-1"></i>Filter Personel</label>
                <select name="user_id" class="form-control form-control-sm shadow-sm">
                    <option value="all">Semua Personel</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-dark"><i class="fas fa-map-marker-alt mr-1"></i>Filter POS</label>
                <select name="pos_id" class="form-control form-control-sm shadow-sm">
                    <option value="all">Semua POS</option>
                    @foreach($positions as $p)
                    <option value="{{ $p->id }}" {{ $posId == $p->id ? 'selected' : '' }}>{{ $p->nama_pos }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 text-right mt-3">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm font-weight-bold">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ request()->fullUrlWithQuery(['download' => 'csv']) }}" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm font-weight-bold">
                    <i class="fas fa-file-csv mr-2"></i>Unduh CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="small font-weight-bold">TANGGAL</th>
                        <th class="small font-weight-bold">NAMA / NIP</th>
                        <th class="small font-weight-bold">POS JAGA</th>
                        <th class="small font-weight-bold">MASUK</th>
                        <th class="small font-weight-bold">PULANG</th>
                        <th class="small font-weight-bold">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td>
                            <div class="font-weight-bold">{{ $a->user?->nama }}</div>
                            <small class="text-muted">{{ $a->user?->nip }}</small>
                        </td>
                        <td>{{ $a->user?->posLokasi?->nama_pos ?? '-' }}</td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td>{{ $a->jam_pulang ?? '-' }}</td>
                        <td>
                            @if($a->terlambat === 'Ya')
                                <span class="badge-late">Terlambat ({{ $a->formatted_terlambat }})</span>
                            @else
                                <span class="badge-ok">Tepat Waktu</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data ditemukan untuk filter ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
