@extends('layouts.app')

@section('content')
@include('layouts.SidebarPabrik')
<div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-title">Detail PO Pembelian</h5>
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
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Informasi PO</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">No. PO</th>
                            <td>
                                @if($isApproved)
                                    {{ $pembelian->getNoPoBeli() }}
                                @else
                                    DRAFT-PO-{{ $pembelian->id_pembelian }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ date('d/m/Y', strtotime($pembelian->tanggal_pembelian)) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($pembelian->status === 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($pembelian->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($pembelian->status === 'canceled')
                                    <span class="badge bg-danger">Canceled</span>
                                @elseif($pembelian->status === 'amended')
                                    <span class="badge bg-info">Amended</span>
                                @elseif($pembelian->status === 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Informasi Pemasok</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nama</th>
                            <td>{{ $pembelian->pemasok->nama_pemasok }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $pembelian->pemasok->alamat_pemasok }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $pembelian->pemasok->telepon_pemasok }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h6>Detail Item</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Item</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailPembelian as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->item->nama_item }}</td>
                                        <td>{{ $detail->jumlah_beli }}</td>
                                        <td>Rp {{ number_format($detail->harga_beli_satuan, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($detail->subtotal_harga, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th>Rp {{ number_format($pembelian->total_harga_pembelian, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pabrik.po-beli') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            
                            <div class="btn-group">
                                @if($pembelian->status === 'draft')
                                    <a href="{{ route('pabrik.po-beli.edit', $pembelian->id_pembelian) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('pabrik.po-beli.approve', $pembelian->id_pembelian) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" 
                                                onclick="return confirm('Yakin ingin menyetujui PO ini?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('pabrik.po-beli.cancel', $pembelian->id_pembelian) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Yakin ingin membatalkan PO ini?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                @elseif($pembelian->status === 'approved')
                                    <a href="{{ route('pabrik.po-beli.edit-approved', $pembelian->id_pembelian) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('pabrik.po-beli.complete', $pembelian->id_pembelian) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" 
                                                onclick="return confirm('Yakin ingin menyelesaikan PO ini?')">
                                            <i class="fas fa-check-double"></i> Selesai
                                        </button>
                                    </form>
                                    <form action="{{ route('pabrik.po-beli.cancel-approved', $pembelian->id_pembelian) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Yakin ingin membatalkan PO ini?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('pabrik.po-beli.print-detail', $pembelian->id_pembelian) }}" 
                       class="btn btn-secondary" target="_blank">
                        <i class="fas fa-print"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 