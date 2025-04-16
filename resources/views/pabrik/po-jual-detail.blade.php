@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Detail PO Penjualan #{{ $penjualan->id_penjualan }}</h4>
            <a href="{{ route('pabrik.po-jual') }}" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Penjualan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            @if($isApproved && count($detailPenjualan) > 0)
                                <dt class="col-sm-4">Nomor PO</dt>
                                <dd class="col-sm-8">{{ $detailPenjualan->first()->no_po_jual }}</dd>
                            @endif
                            
                            <dt class="col-sm-4">ID Penjualan</dt>
                            <dd class="col-sm-8">{{ $penjualan->id_penjualan }}</dd>
                            
                            <dt class="col-sm-4">Pelanggan</dt>
                            <dd class="col-sm-8">{{ $penjualan->pelanggan->nama_pelanggan }}</dd>
                            
                            <dt class="col-sm-4">Tanggal</dt>
                            <dd class="col-sm-8">{{ date('d F Y', strtotime($penjualan->tanggal_penjualan)) }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Total Harga</dt>
                            <dd class="col-sm-8">Rp {{ number_format($penjualan->total_harga_penjualan, 0, ',', '.') }}</dd>
                            
                            <dt class="col-sm-4">Karyawan</dt>
                            <dd class="col-sm-8">{{ $penjualan->karyawan->nama_karyawan }}</dd>
                            
                            <dt class="col-sm-4">Jumlah Item</dt>
                            <dd class="col-sm-8">{{ $detailPenjualan->count() }} item</dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @if($isApproved)
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Item</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Detail</th>
                                <th>Nama Item</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailPenjualan as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->id_detail_penjualan }}</td>
                                    <td>{{ $detail->item->nama_item }}</td>
                                    <td>{{ $detail->jumlah_jual }}</td>
                                    <td>Rp {{ number_format($detail->harga_jual_satuan, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->subtotal_harga, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total</th>
                                <th>Rp {{ number_format($penjualan->total_harga_penjualan, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection