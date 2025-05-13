@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daftar PO Pembelian</h5>
                    <a href="{{ route('pabrik.po-beli.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> Buat PO Baru
                    </a>
                </div>
                <div class="card-body">
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No. PO</th>
                                    <th>Tanggal</th>
                                    <th>Pemasok</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pembelian as $po)
                                    <tr>
                                        <td>
                                            @if($po->status === 'draft')
                                                DRAFT-PO-{{ $po->id_pembelian }}
                                            @else
                                                {{ $po->getNoPoBeli() }}
                                            @endif
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime($po->tanggal_pembelian)) }}</td>
                                        <td>{{ $po->pemasok->nama_pemasok }}</td>
                                        <td>Rp {{ number_format($po->total_harga_pembelian, 0, ',', '.') }}</td>
                                        <td>
                                            @if($po->status === 'draft')
                                                <span class="badge bg-warning">Draft</span>
                                            @elseif($po->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($po->status === 'canceled')
                                                <span class="badge bg-danger">Canceled</span>
                                            @elseif($po->status === 'amended')
                                                <span class="badge bg-info">Amended</span>
                                            @elseif($po->status === 'completed')
                                                <span class="badge bg-primary">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('pabrik.po-beli.show', $po->id_pembelian) }}" 
                                                   class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($po->status === 'draft')
                                                    <a href="{{ route('pabrik.po-beli.edit', $po->id_pembelian) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pabrik.po-beli.approve', $po->id_pembelian) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                title="Approve" onclick="return confirm('Yakin ingin menyetujui PO ini?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('pabrik.po-beli.cancel', $po->id_pembelian) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Cancel" onclick="return confirm('Yakin ingin membatalkan PO ini?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @elseif($po->status === 'approved')
                                                    <a href="{{ route('pabrik.po-beli.edit-approved', $po->id_pembelian) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pabrik.po-beli.cancel-approved', $po->id_pembelian) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Cancel" onclick="return confirm('Yakin ingin membatalkan PO ini?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <a href="{{ route('pabrik.po-beli.print-detail', $po->id_pembelian) }}" 
                                                   class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
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
@endsection 