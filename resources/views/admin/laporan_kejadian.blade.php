@extends('layouts.sb-admin')

@section('styles')
    <style>
        .report-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .report-img:hover {
            transform: scale(1.05);
        }

        #lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, .9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        #lightbox.show {
            display: flex;
        }

        #lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Kejadian Personel</h1>
        <a href="{{ route('admin.laporan.print', ['start_date' => $startDate, 'end_date' => $endDate, 'user_id' => $userId]) }}"
            target="_blank" class="btn btn-primary shadow-sm font-weight-bold">
            <i class="fas fa-print mr-2"></i>Cetak Laporan
        </a>
    </div>

    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom-0">
            <form method="get" class="form-row align-items-end">
                <div class="col-md-5">
                    <label class="small font-weight-bold text-dark"><i class="fas fa-calendar-alt mr-1"></i>Periode
                        Laporan</label>
                    <div class="d-flex align-items-center">
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="form-control form-control-sm shadow-sm">
                        <span class="mx-2 text-muted small font-weight-bold">s/d</span>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="form-control form-control-sm shadow-sm">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="small font-weight-bold text-dark"><i class="fas fa-user-shield mr-1"></i>Nama
                        Personel</label>
                    <select name="user_id" class="form-control form-control-sm shadow-sm">
                        <option value="all">Semua Personel</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm font-weight-bold">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="small font-weight-bold">WAKTU / PERSONEL</th>
                            <th class="small font-weight-bold">KEJADIAN / TEMPAT</th>
                            <th class="small font-weight-bold">FOTO BUKTI</th>
                            <th class="text-center small font-weight-bold">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $l)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-primary">
                                        {{ \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') }}</div>
                                    <div class="small font-weight-bold">{{ $l->jam }}</div>
                                    <hr class="my-1">
                                    <div class="small text-muted font-weight-bold">{{ $l->user?->nama }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold mb-1">{{ $l->judul }}</div>
                                    <p class="small text-muted mb-2">{{ $l->deskripsi }}</p>
                                    <span class="badge badge-light border text-muted px-2"><i
                                            class="fas fa-map-marker-alt mr-1"></i>{{ $l->user?->posLokasi?->nama_pos ?? 'Patroli Luar' }}</span>
                                </td>
                                <td>
                                    @if($l->foto)
                                        <img src="{{ asset('storage/' . $l->foto) }}" class="report-img shadow-sm"
                                            onclick="viewImg(this.src)">
                                    @else
                                        <span class="text-muted small">Tanpa Foto</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.laporan.delete', $l->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-sm text-danger shadow-sm border confirm-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Tidak ada laporan kejadian untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Lightbox --}}
    <div id="lightbox" onclick="this.classList.remove('show')">
        <img id="lb-img" src="" alt="">
    </div>
@endsection

@section('scripts')
    <script>
        function viewImg(src) { document.getElementById('lb-img').src = src; document.getElementById('lightbox').classList.add('show'); }
    </script>
@endsection