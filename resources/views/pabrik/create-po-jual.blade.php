@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
        <h4 class="fw-bold mb-4">Buat PO Jual</h4>
        
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
                <form action="{{ route('pabrik.po-jual.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="id_detail_sales" class="form-label">ID Detail Penjualan</label>
                        <input type="text" class="form-control bg-light" id="id_detail_sales" value="Otomatis" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="id_sales" class="form-label">ID Penjualan</label>
                        <input type="text" class="form-control bg-light" id="id_sales" value="Otomatis" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="item_id" class="form-label">ID Item</label>
                        <select name="item_id" class="form-select" id="item_id" required>
                            <option value="">Pilih Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id_item }}">{{ $item->nama_item }}</option>
                            @endforeach
                        </select>
                        @error('item_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah Jual</label>
                        <input type="number" name="quantity" class="form-control" id="quantity" placeholder="Masukkan Kuantitas" required>
                        @error('quantity')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="unit_price" class="form-label">Harga Jual Satuan</label>
                        <input type="number" name="unit_price" class="form-control" id="unit_price" placeholder="Masukkan Harga Satuan" required>
                        @error('unit_price')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subtotal_price" class="form-label">Subtotal Harga</label>
                        <input type="text" class="form-control bg-light" id="subtotal_price" value="Otomatis" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">ID Pelanggan</label>
                        <select name="customer_id" class="form-select" id="customer_id" required>
                            <option value="">Pilih Pelanggan</option>
                            @foreach($pelanggan as $p)
                                <option value="{{ $p->id_pelanggan }}">{{ $p->nama_pelanggan }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sale_date" class="form-label">Tanggal Penjualan</label>
                        <input type="date" class="form-control bg-light" id="sale_date" value="{{ date('Y-m-d') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="total_sale_price" class="form-label">Total Harga Penjualan</label>
                        <input type="text" class="form-control bg-light" id="total_sale_price" value="Otomatis" readonly>
                        <small class="form-text text-muted">Total harga setelah diskon (jika ada)</small>
                    </div>

                    <div class="mb-3">
                        <label for="employee_id" class="form-label">ID Karyawan</label>
                        <select name="employee_id" class="form-select" id="employee_id" required>
                            <option value="">Pilih Karyawan</option>
                            @foreach($karyawan as $k)
                                <option value="{{ $k->id_karyawan }}">{{ $k->nama_karyawan }}</option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Buat PO</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const quantityInput = document.getElementById('quantity');
        const unitPriceInput = document.getElementById('unit_price');
        const subtotalPriceInput = document.getElementById('subtotal_price');
        const totalSalePriceInput = document.getElementById('total_sale_price');

        function calculateSubtotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const subtotal = quantity * unitPrice;
            
            // Format ke format rupiah
            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });
            
            subtotalPriceInput.value = formatter.format(subtotal);
            totalSalePriceInput.value = formatter.format(subtotal); // Asumsi tidak ada diskon
        }

        quantityInput.addEventListener('input', calculateSubtotal);
        unitPriceInput.addEventListener('input', calculateSubtotal);
    </script>
@endsection