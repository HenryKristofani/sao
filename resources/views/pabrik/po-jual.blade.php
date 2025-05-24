@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-top: 60px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Daftar PO Penjualan</h5>
            <a href="{{ route('pabrik.po-jual.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat PO Baru
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Aksi</th>
                                <th>No. PO</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualan as $p)
                                <tr>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('pabrik.po-jual.show', $p->id_penjualan) }}" 
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($p->status == 'draft')
                                                <a href="{{ route('pabrik.po-jual.edit', $p->id_penjualan) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('pabrik.po-jual.approve', $p->id_penjualan) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Approve" onclick="return confirm('Yakin ingin menyetujui PO ini?')">
                                                        <i class="fas fa-thumbs-up"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('pabrik.po-jual.cancel', $p->id_penjualan) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Cancel" onclick="return confirm('Yakin ingin membatalkan PO ini?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @elseif($p->status == 'approved')
                                                <a href="{{ route('pabrik.po-jual.edit-approved', $p->id_penjualan) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit"
                                                   onclick="return confirm('Mengedit PO yang sudah diapprove akan mengubah status PO ini menjadi Amended dan membuat draft PO baru. Lanjutkan?')">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <a href="{{ route('pabrik.po-jual.surat-jalan', $p->id_penjualan) }}" 
                                                   class="btn btn-sm btn-primary" title="Surat Jalan" target="_blank">
                                                    <i class="fas fa-truck"></i>
                                                </a>
                                                
                                                <a href="{{ route('pabrik.po-jual.invoice', $p->id_penjualan) }}" 
                                                   class="btn btn-sm btn-success" title="Invoice" target="_blank">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                                
                                                <form action="{{ route('pabrik.po-jual.complete', $p->id_penjualan) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-dark" 
                                                            title="Selesai" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan PO ini? Barang akan dihapus dari gudang perjalanan secara permanen.')">
                                                        <i class="fas fa-flag-checkered"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('pabrik.po-jual.cancel-approved', $p->id_penjualan) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Cancel" onclick="return confirm('Apakah Anda yakin ingin membatalkan PO yang sudah diapprove?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($p->status == 'approved' || $p->status == 'canceled' || $p->status == 'amended' || $p->status == 'completed')
                                            {{ $p->getNoPoJual() }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($p->tanggal_penjualan)) }}</td>
                                    <td>{{ $p->pelanggan->nama_pelanggan }}</td>
                                    <td>Rp {{ number_format($p->total_harga_penjualan, 0, ',', '.') }}</td>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data PO penjualan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection