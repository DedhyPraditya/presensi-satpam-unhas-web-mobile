<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Kejadian - Satpam UNHAS</title>
    <link rel="stylesheet" href="{{ asset('sb-admin-2/css/sb-admin-2.min.css') }}">
    <style>
        body { background: white; color: black; font-family: 'Times New Roman', Times, serif; }
        .kop-surat { border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .logo-unhas { width: 80px; }
        .table th { background: #eee !important; color: black !important; border: 1px solid #000 !important; }
        .table td { border: 1px solid #000 !important; }
        .foto { width: 120px; height: 120px; object-fit: cover; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            .container-fluid { padding: 0 !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container-fluid py-4">
        <div class="no-print mb-4 text-center">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold">
                <i class="fas fa-print mr-2"></i>Klik di sini jika dialog cetak tidak otomatis muncul
            </button>
            <button onclick="window.close()" class="btn btn-light btn-sm border shadow-sm px-4">Tutup Halaman</button>
        </div>

        <div class="kop-surat d-flex align-items-center">
            <img src="{{ asset('img/logo_unhas.png') }}" class="logo-unhas mr-4">
            <div class="text-center flex-grow-1">
                <h4 class="mb-0 font-weight-bold">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h4>
                <h3 class="mb-0 font-weight-bold">UNIVERSITAS HASANUDDIN</h3>
                <h5 class="mb-0 font-weight-bold">SATUAN PENGAMANAN (SATPAM)</h5>
                <small>Jl. Perintis Kemerdekaan KM.10 Tamalanrea, Makassar. Telp: (0411) 586200</small>
            </div>
        </div>

        <div class="text-center mb-4">
            <h5 class="font-weight-bold mb-0">LAPORAN KEJADIAN PERSONEL SATPAM</h5>
            <p class="mb-0">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            @if($selectedUser)
                <p class="mt-0 font-weight-bold text-uppercase">Personel: {{ $selectedUser->nama }}</p>
            @endif
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="50" class="text-center">NO</th>
                    <th width="150" class="text-center">WAKTU / POS</th>
                    <th width="150" class="text-center">NAMA PERSONEL</th>
                    <th class="text-center">JUDUL & DESKRIPSI KEJADIAN</th>
                    <th width="140" class="text-center">DOKUMENTASI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $index => $l)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        <div>{{ \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') }}</div>
                        <div class="font-weight-bold">{{ $l->jam }}</div>
                        <hr class="my-1">
                        <small>{{ $l->user?->posLokasi?->nama_pos ?? 'Patroli' }}</small>
                    </td>
                    <td class="text-center">{{ $l->user?->nama }}</td>
                    <td>
                        <div class="font-weight-bold mb-1">{{ $l->judul }}</div>
                        <div style="font-size: 13px;">{{ $l->deskripsi }}</div>
                    </td>
                    <td class="text-center">
                        @if($l->foto)
                            <img src="{{ asset('storage/'.$l->foto) }}" class="foto">
                        @else
                            <small class="text-muted italic">Tidak ada dokumentasi</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-5 d-flex justify-content-end">
            <div class="text-center" style="width: 250px;">
                <p class="mb-5">Makassar, {{ \Carbon\Carbon::now()->format('d F Y') }}<br>Kepala Satuan Pengamanan,</p>
                <br><br>
                <p class="font-weight-bold mb-0">( ............................................ )</p>
                <p class="small">NIP/NIK. ....................................</p>
            </div>
        </div>
    </div>
</body>
</html>
