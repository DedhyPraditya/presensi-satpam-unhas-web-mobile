@extends('layouts.sb-admin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Verifikasi & Manajemen User</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Tab Navigation -->
        <div class="col-12 mb-4">
            <ul class="nav nav-pills bg-white p-2 rounded shadow-sm" id="userTab" role="tablist">
                <li class="nav-item mr-2">
                    <a class="nav-link active font-weight-bold" id="pending-tab" data-toggle="pill" href="#pending"
                        role="tab">
                        Menunggu Verifikasi <span class="badge badge-warning ml-1">{{ $pendingUsers->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="verified-tab" data-toggle="pill" href="#verified" role="tab">
                        User Terverifikasi <span class="badge badge-success ml-1">{{ $verifiedUsers->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-12">
            <div class="tab-content" id="userTabContent">
                <!-- Pending Users -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="card shadow border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nama / NIP</th>
                                            <th>Pos Jaga</th>
                                            <th>Jenis Kerja</th>
                                            <th>Tgl Daftar</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingUsers as $u)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $u->nama }}</div>
                                                    <small class="text-muted">{{ $u->nip }}</small>
                                                </td>
                                                <td>{{ $u->posLokasi?->nama_pos ?? '-' }}</td>
                                                <td><span class="badge badge-secondary">{{ $u->jenis_kerja }}</span></td>
                                                <td>{{ $u->created_at->format('d M Y') }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center" style="gap:5px;">
                                                        <form action="{{ route('verifikasi.user.verify', $u->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-success btn-sm font-weight-bold px-3 rounded-pill shadow-sm">
                                                                <i class="fas fa-check-circle mr-1"></i> Verifikasi
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('verifikasi.user.reject', $u->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm px-3 rounded-pill shadow-sm confirm-delete"
                                                                data-confirm="Tolak pendaftaran akun {{ $u->nama }}?"
                                                                data-confirm-text="Ya, Tolak!" data-icon="question">
                                                                <i class="fas fa-times-circle mr-1"></i> Tolak
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">Tidak ada user menunggu
                                                    verifikasi</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verified Users -->
                <div class="tab-pane fade" id="verified" role="tabpanel">
                    <div class="card shadow border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nama / NIP</th>
                                            <th>Pos Jaga</th>
                                            <th>Jenis Kerja</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($verifiedUsers as $u)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $u->nama }}</div>
                                                    <small class="text-muted">{{ $u->nip }}</small>
                                                </td>
                                                <td>{{ $u->posLokasi?->nama_pos ?? '-' }}</td>
                                                <td><span class="badge badge-info">{{ $u->jenis_kerja }}</span></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center" style="gap:5px;">
                                                        <button
                                                            class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-bold"
                                                            data-toggle="modal" data-target="#editUser{{ $u->id }}">
                                                            <i class="fas fa-user-edit mr-1"></i> Edit
                                                        </button>
                                                        <form action="{{ route('admin.user.unverify', $u->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm font-weight-bold text-white">
                                                                <i class="fas fa-undo-alt mr-1"></i> Batal Verifikasi
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.user.destroy', $u->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-light btn-sm rounded-circle shadow-sm border confirm-delete"
                                                                style="width:30px; height:30px;"
                                                                data-confirm="Hapus user {{ $u->nama }} secara permanen?"
                                                                data-confirm-text="Ya, Hapus!">
                                                                <i class="fas fa-trash text-danger"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Collection (Pindahkan ke luar tabel agar tidak pecah) --}}
    @foreach($verifiedUsers as $u)
        <div class="modal fade" id="editUser{{ $u->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form action="{{ route('admin.user.update', $u->id) }}" method="POST"
                    class="modal-content border-0 shadow-lg bg-white">
                    @csrf @method('PUT')
                    <div class="modal-header bg-light border-bottom-0 py-3">
                        <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit mr-2 text-primary"></i>Edit
                            Data Personel</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Nama Lengkap</label>
                            <input type="text" name="nama" value="{{ $u->nama }}" class="form-control shadow-sm border-faded"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">NIP</label>
                            <input type="text" name="nip" value="{{ $u->nip }}" class="form-control shadow-sm border-faded">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-dark">Pos Jaga Utama</label>
                            <select name="id_pos" class="form-control shadow-sm border-faded">
                                <option value="">-- Tanpa Pos --</option>
                                @foreach($positions as $p)
                                    <option value="{{ $p->id }}" {{ $u->id_pos == $p->id ? 'selected' : '' }}>{{ $p->nama_pos }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-dark">Sistem Kerja</label>
                            <select name="jenis_kerja" class="form-control shadow-sm border-faded">
                                <option value="non_shift" {{ $u->jenis_kerja == 'non_shift' ? 'selected' : '' }}>Non-Shift
                                    (Pagi-Sore)</option>
                                <option value="shift" {{ $u->jenis_kerja == 'shift' ? 'selected' : '' }}>Shift (Pagi/Malam)
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm"
                            data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 font-weight-bold shadow-sm">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection