@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
        <h4 class="fw-bold mb-4">Buat PO Jual</h4>

        <div class="card">
            <div class="card-body">
                <form>
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
                        <select class="form-select" id="item_id">
                            <option value="">Pilih Item</option>
                            <option value="item1">Nama Item 1</option>
                            <option value="item2">Nama Item 2</option>
                            <option value="item3">Nama Item 3</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah Jual</label>
                        <input type="number" class="form-control" id="quantity" placeholder="Masukkan Kuantitas">
                    </div>

                    <div class="mb-3">
                        <label for="unit_price" class="form-label">Harga Jual Satuan</label>
                        <input type="text" class="form-control" id="unit_price" placeholder="Masukkan Harga Satuan">
                    </div>

                    <div class="mb-3">
                        <label for="subtotal_price" class="form-label">Subtotal Harga</label>
                        <input type="text" class="form-control bg-light" id="subtotal_price" value="Otomatis" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">ID Pelanggan</label>
                        <select class="form-select" id="customer_id">
                            <option value="">Pilih Pelanggan</option>
                            <option value="cust1">Nama Pelanggan A</option>
                            <option value="cust2">Nama Pelanggan B</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="sale_date" class="form-label">Tanggal Penjualan</label>
                        <input type="date" class="form-control bg-light" id="sale_date" value="{{ date('Y-m-d') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="total_sale_price" class="form-label">Total Harga Penjualan</label>
                        <input type="text" class="form-control bg-light" id="total_sale_price" value="Otomatis" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="employee_id" class="form-label">ID Karyawan</label>
                        <select class="form-select" id="employee_id">
                            <option value="">Pilih Karyawan</option>
                            <option value="emp1">Nama Karyawan X</option>
                            <option value="emp2">Nama Karyawan Y</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary">Buat PO</button>
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
            subtotalPriceInput.value = subtotal.toFixed(2);
            totalSalePriceInput.value = subtotal.toFixed(2); // Asumsi hanya satu item untuk saat ini
        }

        quantityInput.addEventListener('input', calculateSubtotal);
        unitPriceInput.addEventListener('input', calculateSubtotal);
    </script>
@endsection