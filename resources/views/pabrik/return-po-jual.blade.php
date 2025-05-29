@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-top: 60px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Return PO Penjualan #{{ $penjualan->id_penjualan }}</h4>
            <a href="{{ route('pabrik.po-jual') }}" class="btn btn-secondary">Kembali</a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi PO</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Nomor PO</dt>
                            <dd class="col-sm-8">{{ $penjualan->getNoPoJual() }}</dd>
                            
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
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Item untuk Return</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pabrik.po-jual.process-return', $penjualan->id_penjualan) }}" method="POST" id="returnForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Item</th>
                                    <th>Jumlah Awal</th>
                                    <th>Jumlah Return</th>
                                    <th>Return Semua</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailPenjualan as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->item->nama_item }}</td>
                                        <td>{{ $detail->jumlah_jual }}</td>
                                        <td>
                                            <input type="number" 
                                                   name="return_items[{{ $index }}][quantity]" 
                                                   class="form-control return-quantity" 
                                                   min="1" 
                                                   max="{{ $detail->jumlah_jual }}"
                                                   value="{{ $detail->jumlah_jual }}"
                                                   {{ $detail->return_all ? 'disabled' : '' }}>
                                            <input type="hidden" 
                                                   name="return_items[{{ $index }}][id_detail_penjualan]" 
                                                   value="{{ $detail->id_detail_penjualan }}">
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       class="form-check-input return-all-checkbox" 
                                                       name="return_items[{{ $index }}][return_all]" 
                                                       value="1"
                                                       data-index="{{ $index }}">
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Item yang di-return akan dipindahkan ke Gudang Return Client (Lokasi 6)
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin melakukan return untuk item yang dipilih?')">
                            Proses Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle return all checkbox
            document.querySelectorAll('.return-all-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const index = this.dataset.index;
                    const quantityInput = document.querySelector(`input[name="return_items[${index}][quantity]"]`);
                    
                    if (this.checked) {
                        quantityInput.disabled = true;
                        quantityInput.value = quantityInput.max;
                    } else {
                        quantityInput.disabled = false;
                    }
                });
            });

            // Form validation
            document.getElementById('returnForm').addEventListener('submit', function(event) {
                let hasReturn = false;
                
                document.querySelectorAll('.return-all-checkbox').forEach(function(checkbox) {
                    if (checkbox.checked) {
                        hasReturn = true;
                    }
                });

                document.querySelectorAll('.return-quantity').forEach(function(input) {
                    if (parseInt(input.value) > 0) {
                        hasReturn = true;
                    }
                });

                if (!hasReturn) {
                    event.preventDefault();
                    alert('Pilih minimal satu item untuk di-return');
                }
            });
        });
    </script>
@endsection 