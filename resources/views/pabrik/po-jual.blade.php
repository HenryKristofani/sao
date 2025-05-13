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
                            <th>No PO</th>
                            <th>ID Penjualan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Jumlah Item</th>
                            <th>Total Harga</th>
                            <th>Karyawan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($penjualan as $p)
                        <tr>
                        <td>
                            @if($p->status == 'approved' || $p->status == 'canceled' || $p->status == 'amended' || $p->status == 'completed')
                                {{ $p->getNoPoJual() }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                            <td>{{ $p->id_penjualan }}</td>
                            <td>{{ $p->pelanggan->nama_pelanggan }}</td>
                            <td>{{ $p->tanggal_penjualan }}</td>
                            <td>{{ $p->detailPenjualan->count() }} item</td>
                            <td>Rp {{ number_format($p->total_harga_penjualan, 0, ',', '.') }}</td>
                            <td>{{ $p->karyawan->nama_karyawan }}</td>
                            <td>
                                @if($p->status == 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($p->status == 'canceled')
                                    <span class="badge bg-danger">Canceled</span>
                                @elseif($p->status == 'amended')
                                    <span class="badge bg-info">Amended</span>
                                @elseif($p->status == 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @else
                                    <span class="badge bg-success">Approved</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('pabrik.po-jual.show', $p->id_penjualan) }}" class="btn btn-sm btn-info">Detail</a>
                                
                                @if($p->status == 'draft')
                                    <a href="{{ route('pabrik.po-jual.edit', $p->id_penjualan) }}" class="btn btn-sm btn-warning">Edit</a>
                                    
                                    <!-- Approve Button -->
                                    <form action="{{ route('pabrik.po-jual.approve', $p->id_penjualan) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Apakah Anda yakin ingin approve PO ini?')">Approve</button>
                                    </form>
                                    
                                    <!-- Cancel Button for Draft -->
                                    <form action="{{ route('pabrik.po-jual.cancel', $p->id_penjualan) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan PO ini?')">Cancel</button>
                                    </form>
                                @elseif($p->status == 'approved')
                                    <!-- Edit Button for approved POs -->
                                    <a href="{{ route('pabrik.po-jual.edit-approved', $p->id_penjualan) }}" class="btn btn-sm btn-warning" onclick="return confirm('Mengedit PO yang sudah diapprove akan mengubah status PO ini menjadi Amended dan membuat draft PO baru. Lanjutkan?')">Edit</a>
                                    
                                    <!-- Surat Jalan Button for approved POs -->
                                    <a href="{{ route('pabrik.po-jual.surat-jalan', $p->id_penjualan) }}" class="btn btn-sm btn-primary" target="_blank">Surat Jalan</a>
                                    
                                    <!-- Invoice Button for approved POs -->
                                    <a href="{{ route('pabrik.po-jual.invoice', $p->id_penjualan) }}" class="btn btn-sm btn-success" target="_blank">Invoice</a>
                                    
                                    <!-- Complete Button for approved POs -->
                                    <form action="{{ route('pabrik.po-jual.complete', $p->id_penjualan) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan PO ini? Barang akan dihapus dari gudang perjalanan secara permanen.')">Selesai</button>
                                    </form>
                                    
                                    <!-- Cancel Approved PO Button -->
                                    <form action="{{ route('pabrik.po-jual.cancel-approved', $p->id_penjualan) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan PO yang sudah diapprove?')">Cancel</button>
                                    </form>
                                @elseif($p->status == 'canceled')
                                    <!-- No action buttons for canceled POs -->
                                @elseif($p->status == 'amended')
                                    <!-- No action buttons for amended POs -->
                                @elseif($p->status == 'completed')
                                    <!-- No action buttons for completed POs -->
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data PO penjualan</td>
                        </tr>
                    @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection