@extends('layouts.sb-admin')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #map { height: 400px; border-radius: 12px; z-index: 1; }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit POS Jaga</h1>
    <a href="{{ route('tambah.pos') }}" class="btn btn-light shadow-sm border small font-weight-bold">
        <i class="fas fa-arrow-left mr-2"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit mr-2"></i>Perbarui Data POS: {{ $position->nama_pos }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tambah.pos.update', $position->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold small">Nama POS Jaga</label>
                                <input type="text" name="nama_pos" value="{{ $position->nama_pos }}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small">Radius (Meter)</label>
                                <input type="number" name="radius" id="radius-input" value="{{ $position->radius }}" class="form-control" min="10" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small">Latitude</label>
                                <input type="text" name="latitude" id="lat" value="{{ $position->latitude }}" class="form-control" required readonly>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small">Longitude</label>
                                <input type="text" name="longitude" id="lng" value="{{ $position->longitude }}" class="form-control" required readonly>
                            </div>
                            <small class="text-muted d-block mb-3">* Seret marker pada peta untuk mengubah koordinat</small>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm py-2">
                                <i class="fas fa-save mr-2"></i>Perbarui POS Jaga
                            </button>
                        </div>
                        <div class="col-md-7">
                            <div id="map"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var lat = {{ $position->latitude }};
    var lng = {{ $position->longitude }};
    var rad = {{ $position->radius }};

    var map = L.map('map').setView([lat, lng], 17);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);

    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    var circle = L.circle([lat, lng], { radius: rad, color: 'var(--primary)', fillOpacity: 0.1 }).addTo(map);

    marker.on('dragend', function(e) {
        var pos = marker.getLatLng();
        document.getElementById('lat').value = pos.lat.toFixed(6);
        document.getElementById('lng').value = pos.lng.toFixed(6);
        circle.setLatLng(pos);
    });

    document.getElementById('radius-input').addEventListener('input', function() {
        circle.setRadius(this.value);
    });
</script>
@endsection
