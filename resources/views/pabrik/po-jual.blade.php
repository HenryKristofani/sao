@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">PO Penjualan</h4>
            <a href="{{ route('pabrik.po-jual.create') }}" class="btn btn-primary">Buat PO Baru</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Penjualan</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Jumlah Item</th>
                                <th>Total Harga</th>
                                <th>Karyawan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualan as $p)
                                <tr>
                                    <td>{{ $p->id_penjualan }}</td>
                                    <td>{{ $p->pelanggan->nama_pelanggan }}</td>
                                    <td>{{ $p->tanggal_penjualan }}</td>
                                    <td>{{ $p->detailPenjualan->count() }} item</td>
                                    <td>Rp {{ number_format($p->total_harga_penjualan, 0, ',', '.') }}</td>
                                    <td>{{ $p->karyawan->nama_karyawan }}</td>
                                    <td>
                                        <a href="{{ route('pabrik.po-jual.show', $p->id_penjualan) }}" class="btn btn-sm btn-info">Detail</a>
                                        <a href="{{ route('pabrik.po-jual.edit', $p->id_penjualan) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <!-- Cancel Button -->
                                        <form action="{{ route('pabrik.po-jual.cancel', $p->id_penjualan) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan PO ini?')">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data PO penjualan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection