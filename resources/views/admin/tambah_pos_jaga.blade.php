@extends('layouts.sb-admin')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #map { height: 400px; border-radius: 12px; z-index: 1; }
    .pos-card { transition: all 0.2s; border: none; }
    .pos-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen POS Jaga</h1>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

<div class="row">
    <!-- Map & Form -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle mr-2"></i>Tambah POS Baru</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tambah.pos.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Nama POS Jaga</label>
                                <input type="text" name="nama_pos" class="form-control" placeholder="Contoh: POS Pintu 1" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small">Radius (Meter)</label>
                                <input type="number" name="radius" class="form-control" value="50" min="10" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Latitude</label>
                                        <input type="text" name="latitude" id="lat" class="form-control" required readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small">Longitude</label>
                                        <input type="text" name="longitude" id="lng" class="form-control" required readonly>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-3">* Klik pada peta untuk menentukan koordinat</small>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm py-2">
                                <i class="fas fa-save mr-2"></i>Simpan POS Jaga
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div id="map"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Side List -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-2"></i>Daftar POS Terdaftar</h6>
            </div>
            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                <div class="list-group list-group-flush">
                    @forelse($positions as $p)
                    <div class="list-group-item list-group-item-action py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 font-weight-bold text-gray-800">{{ $p->nama_pos }}</h6>
                                <p class="mb-0 small text-muted">Radius: {{ $p->radius }}m</p>
                            </div>
                            <div class="d-flex" style="gap:5px;">
                                <a href="{{ route('tambah.pos.edit', $p->id) }}" class="btn btn-light btn-sm"><i class="fas fa-edit text-primary"></i></a>
                                <form action="{{ route('tambah.pos.destroy', $p->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm confirm-delete" 
                                        data-confirm="Hapus POS Jaga {{ $p->nama_pos }}?"
                                        data-confirm-text="Ya, Hapus!">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted small">Belum ada POS jaga terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-5.1486, 119.4320], 14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);

    var marker;
    var circle;

    map.on('click', function(e) {
        if (marker) map.removeLayer(marker);
        if (circle) map.removeLayer(circle);

        marker = L.marker(e.latlng).addTo(map);
        
        let rad = document.getElementsByName('radius')[0].value;
        circle = L.circle(e.latlng, { radius: rad, color: 'var(--primary)', fillOpacity: 0.1 }).addTo(map);

        document.getElementById('lat').value = e.latlng.lat.toFixed(6);
        document.getElementById('lng').value = e.latlng.lng.toFixed(6);
    });

    document.getElementsByName('radius')[0].addEventListener('change', function() {
        if (circle) {
            circle.setRadius(this.value);
        }
    });

    // Existing Markers
    @foreach($positions as $pos)
        L.circle([{{ $pos->latitude }}, {{ $pos->longitude }}], { radius: {{ $pos->radius }}, color: '#cbd5e1', weight: 1, fillOpacity: 0.05 }).addTo(map);
    @endforeach
</script>
@endsection
