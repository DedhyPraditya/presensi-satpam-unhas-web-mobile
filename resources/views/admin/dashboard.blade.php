@extends('layouts.sb-admin')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
.stat-card { border-left: 4px solid; border-radius: 8px; transition: transform .2s; }
.stat-card:hover { transform: translateY(-3px); }
.stat-card.blue   { border-color: var(--accent); }
.stat-card.green  { border-color: var(--success); }
.stat-card.red    { border-color: var(--primary); }
.stat-card.yellow { border-color: var(--warning); }
.stat-card.indigo { border-color: #6f42c1; }
.stat-icon-lg { font-size: 2rem; opacity:.3; }
.badge-ok   { background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-late { background:#fee2e2; color:#7f1d1d; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-none { background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.thumb { width:46px; height:46px; border-radius:6px; object-fit:cover; cursor:pointer; }
#map-preview { height:340px; border-radius:12px; z-index:1; }
.table th { background:#f8f9fc; font-size:12px; font-weight:700; color:#5a5c69; text-transform:uppercase; letter-spacing:.05em; padding: 12px 15px; }
.table td { font-size:13px; vertical-align:middle; padding: 12px 15px; }
#lightbox { display:none; position:fixed; top:0; left:0; right:0; bottom:0;
  background:rgba(0,0,0,.88); z-index:9999; align-items:center; justify-content:center; }
#lightbox.show { display:flex; }
#lightbox img { max-width:90vw; max-height:90vh; border-radius:10px; }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Ringkasan Dashboard</h1>
        <small class="text-muted">Monitoring kehadiran Satpam Universitas Hasanuddin</small>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }} <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

{{-- Stats --}}
<div class="row mb-4">
    <div class="col-xl col-md-6 mb-4">
        <div class="card shadow h-100 py-2 stat-card blue">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Satpam</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $totalSatpam }}</div>
                        <div style="font-size:10px;color:#94a3b8;">Terverifikasi</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-users stat-icon-lg text-primary"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card shadow h-100 py-2 stat-card green">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hadir Hari Ini</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $hadirToday }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-check stat-icon-lg text-success"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card shadow h-100 py-2 stat-card red">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Terlambat</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $terlambatToday }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-times stat-icon-lg text-danger"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card shadow h-100 py-2 stat-card yellow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Belum Hadir</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $belumHadir }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clock stat-icon-lg text-warning"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card shadow h-100 py-2 stat-card indigo">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#6f42c1;">Lap. Kejadian</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $laporanHariIni }}</div>
                        <div style="font-size:10px;color:#94a3b8;">Hari Ini</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-exclamation-triangle stat-icon-lg" style="color:#6f42c1;opacity:.3;"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pending user alert --}}
@if($pendingCount > 0)
<div class="alert alert-warning d-flex align-items-center justify-content-between border-0 shadow-sm" style="border-radius:12px;">
    <span><i class="fas fa-user-clock mr-2"></i> Ada <b>{{ $pendingCount }}</b> akun baru menunggu verifikasi.</span>
    <a href="{{ route('verifikasi.user') }}" class="btn btn-sm btn-warning font-weight-bold px-3">Verifikasi Sekarang</a>
</div>
@endif

<div class="row">
    {{-- Peta POS --}}
    <div class="col-lg-5 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-map-marker-alt mr-2"></i>Titik Lokasi POS</h6>
                <span class="badge badge-primary px-3 shadow-sm border-0">{{ $posLocations->count() }} POS</span>
            </div>
            <div class="card-body p-2">
                <div id="map-preview"></div>
            </div>
        </div>
    </div>

    {{-- Filter Periode --}}
    <div class="col-lg-7 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clipboard-list mr-2"></i>Log Kehadiran Personel</h6>
                <form method="get" class="d-flex align-items-center gap-2" style="gap:8px;">
                    <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()"
                           class="form-control form-control-sm border-0 bg-light shadow-sm" style="width:140px; border-radius:8px;">
                    <span class="text-muted small font-weight-bold">s/d</span>
                    <input type="date" name="end_date"   value="{{ $endDate }}"   onchange="this.form.submit()"
                           class="form-control form-control-sm border-0 bg-light shadow-sm" style="width:140px; border-radius:8px;">
                </form>
            </div>
            <div class="card-body p-0" style="max-height:480px;overflow-y:auto;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama / NIP</th>
                                <th>POS</th>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Pulang</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $u)
                            @php $absensiMap = $u->absensi->keyBy(fn($a)=>$a->tanggal); @endphp
                            @foreach($period as $tgl)
                                @php $r = $absensiMap[$tgl] ?? null; @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $u->nama }}</div>
                                        <small class="text-muted">{{ $u->nip }}</small>
                                    </td>
                                    <td><small>{{ $u->posLokasi?->nama_pos ?? '-' }}</small></td>
                                    <td><small>{{ \Carbon\Carbon::parse($tgl)->format('d M Y') }}</small></td>
                                    <td>
                                            @if($link_r = $r) @endif
                                            @if($link_r && $link_r->jam_masuk)
                                                <div class="font-weight-bold">{{ $link_r->jam_masuk }}</div>
                                                @if($link_r->terlambat === 'Ya')
                                                    <span class="badge-late">Terlambat 
                                                        @if($link_r->formatted_terlambat)
                                                            ({{ $link_r->formatted_terlambat }})
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="badge-ok">On Time</span>
                                                @endif
                                            @endif
                                    </td>
                                    <td>
                                        @if($r && $r->jam_pulang)
                                            <div class="font-weight-bold">{{ $r->jam_pulang }}</div>
                                            @if($r->cepat_pulang === 'Ya')
                                                <span class="badge-late">Cepat Pulang</span>
                                            @else
                                                <span class="badge-ok">On Time</span>
                                            @endif
                                        @else
                                            <span class="badge-none">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1" style="gap:4px;">
                                            @if($r && $r->foto_masuk)
                                                <img src="{{ asset('storage/'.$r->foto_masuk) }}" class="thumb shadow-sm" onclick="viewImg(this.src)">
                                            @endif
                                            @if($r && $r->foto_pulang)
                                                <img src="{{ asset('storage/'.$r->foto_pulang) }}" class="thumb shadow-sm" onclick="viewImg(this.src)">
                                            @endif
                                            @if(!$r || (!$r->foto_masuk && !$r->foto_pulang))
                                                <small class="text-muted">—</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($r)
                                        <form action="{{ route('admin.absensi.destroy', $r->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger confirm-delete" 
                                                style="border-radius:8px;" 
                                                data-confirm="Reset absensi ini? Data foto dan jam akan dihapus permanen."
                                                data-confirm-text="Ya, Reset!"
                                                title="Reset Absensi">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                        @else
                                        <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="this.classList.remove('show')">
    <img id="lb-img" src="" alt="">
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map-preview').setView([-5.1486, 119.4320], 14);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{attribution:'© OpenStreetMap'}).addTo(map);
@foreach($posLocations as $p)
    L.marker([{{ $p->latitude }},{{ $p->longitude }}]).addTo(map)
      .bindPopup('<b>{{ addslashes($p->nama_pos) }}</b><br>Radius: {{ $p->radius }}m');
    L.circle([{{ $p->latitude }},{{ $p->longitude }}],{radius:{{ $p->radius }},color:'var(--accent)',fillOpacity:.1,weight:1}).addTo(map);
@endforeach

function viewImg(src){document.getElementById('lb-img').src=src;document.getElementById('lightbox').classList.add('show');}
</script>
@endsection
