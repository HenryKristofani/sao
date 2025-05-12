@extends('layouts.app')

@section('content')
<!-- Include Sidebar -->
@include('layouts.SidebarPabrik')

<div class="content" style="margin-top: 60px;"> <!-- Add margin-top to offset navbar -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Daftar Pelanggan</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPelangganModal">
                        + Tambah Pelanggan Baru
                    </button>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Jenis Pelanggan</th>
                                        <th>Alamat</th>
                                        <th>No. Telepon</th>
                                        <th>Email</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pelanggans as $pelanggan)
                                    <tr>
                                        <td>{{ $pelanggan->id_pelanggan }}</td>
                                        <td>{{ $pelanggan->nama_pelanggan }}</td>
                                        <td>{{ $pelanggan->jenisPelanggan->nama_jenis ?? 'Tidak ada' }}</td>
                                        <td>{{ $pelanggan->alamat_pelanggan }}</td>
                                        <td>{{ $pelanggan->no_telp_pelanggan }}</td>
                                        <td>{{ $pelanggan->email_pelanggan }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('pabrik.pelanggan.edit', $pelanggan->id_pelanggan) }}" class="btn btn-warning">
                                                    Edit
                                                </a>
                                                <form action="{{ route('pabrik.pelanggan.destroy', $pelanggan->id_pelanggan) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Anda yakin ingin menghapus pelanggan ini?')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data pelanggan</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Pelanggan Modal -->
<div class="modal fade" id="addPelangganModal" tabindex="-1" aria-labelledby="addPelangganModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPelangganModalLabel">Tambah Pelanggan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pabrik.pelanggan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_jenis" class="form-label">Jenis Pelanggan</label>
                        <select class="form-select" id="id_jenis" name="id_jenis" required>
                            <option value="">Pilih Jenis Pelanggan</option>
                            @foreach($jenisPelanggans as $jenis)
                                <option value="{{ $jenis->id_jenis }}">{{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nama_pelanggan" class="form-label">Nama Pelanggan</label>
                        <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat_pelanggan" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat_pelanggan" name="alamat_pelanggan"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="no_telp_pelanggan" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="no_telp_pelanggan" name="no_telp_pelanggan">
                    </div>
                    <div class="mb-3">
                        <label for="email_pelanggan" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_pelanggan" name="email_pelanggan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
