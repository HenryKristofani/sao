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
                <form action="{{ route('pabrik.po-jual.store') }}" method="POST" id="poForm">
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
                        <input type="date" class="form-control bg-light" name="sale_date" id="sale_date" value="{{ date('Y-m-d') }}" readonly>
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

                    <hr>
                    <h5 class="mb-3">Detail Item</h5>

                    <div id="items-container">
                        <div class="item-row mb-4 border p-3 rounded">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Nama Item</label>
                                    <select name="items[0][item_id]" class="form-select item-select" required>
                                        <option value="">Pilih Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id_item }}" data-price="{{ $item->harga_per_item }}">{{ $item->nama_item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="items[0][quantity]" class="form-control item-quantity" min="1" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Harga Satuan</label>
                                    <input type="number" name="items[0][unit_price]" class="form-control item-price" readonly>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="text" class="form-control item-subtotal" readonly>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button type="button" class="btn btn-sm btn-danger remove-item" style="display: none;">Hapus Item</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-success" id="add-item">Tambah Item</button>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Total:</strong>
                                        <span id="grand-total">Rp 0</span>
                                        <input type="hidden" name="total_price" id="total-price-input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Buat PO</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Counter untuk index item baru
        let itemCounter = 1;
        
// Fungsi untuk menghitung total keseluruhan
function calculateGrandTotal() {
    let grandTotal = 0;
    
    // Ambil semua subtotal dan jumlahkan
    document.querySelectorAll('.item-subtotal').forEach(function(element) {
        const subtotalText = element.value;
        if (subtotalText) {
            // Extract numeric value from formatted currency string
            const subtotal = parseFloat(subtotalText.replace(/[^0-9,-]+/g, '').replace(',', '.')) || 0;
            grandTotal += subtotal;
        }
    });
    
    // Format grand total as Rupiah with thousands separator manually
    const formattedTotal = 'Rp ' + grandTotal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    // Update display and hidden input
    document.getElementById('grand-total').textContent = formattedTotal;
    document.getElementById('total-price-input').value = grandTotal;
}
        
        // Fungsi untuk menghitung subtotal untuk sebuah baris item
        function calculateSubtotal(row) {
            const quantityInput = row.querySelector('.item-quantity');
            const priceInput = row.querySelector('.item-price');
            const subtotalInput = row.querySelector('.item-subtotal');
            
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = quantity * price;
            
            // Format ke format rupiah
            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });
            
            subtotalInput.value = formatter.format(subtotal);
            
            // Update grand total
            calculateGrandTotal();
        }
        
        // Fungsi untuk menambahkan baris item baru
        document.getElementById('add-item').addEventListener('click', function() {
            const itemsContainer = document.getElementById('items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row mb-4 border p-3 rounded';
            
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Nama Item</label>
                        <select name="items[${itemCounter}][item_id]" class="form-select item-select" required>
                            <option value="">Pilih Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id_item }}" data-price="{{ $item->harga_per_item }}">{{ $item->nama_item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="items[${itemCounter}][quantity]" class="form-control item-quantity" min="1" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Harga Satuan</label>
                        <input type="number" name="items[${itemCounter}][unit_price]" class="form-control item-price" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control item-subtotal" readonly>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <button type="button" class="btn btn-sm btn-danger remove-item">Hapus Item</button>
                    </div>
                </div>
            `;
            
            itemsContainer.appendChild(newRow);
            itemCounter++;
            
            // Tampilkan tombol hapus untuk semua baris jika ada lebih dari 1 baris
            toggleRemoveButtons();
            setupEventListeners(newRow);
        });
        
        // Fungsi untuk setup event listeners pada baris item
        function setupEventListeners(row) {
            const itemSelect = row.querySelector('.item-select');
            const quantityInput = row.querySelector('.item-quantity');
            const priceInput = row.querySelector('.item-price');
            const removeButton = row.querySelector('.remove-item');
            
            // Event listener untuk dropdown item
            itemSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const price = selectedOption.getAttribute('data-price');
                    priceInput.value = price;
                    calculateSubtotal(row);
                } else {
                    priceInput.value = '';
                    calculateSubtotal(row);
                }
            });
            
            // Event listener untuk input kuantitas
            quantityInput.addEventListener('input', function() {
                calculateSubtotal(row);
            });
            
            // Event listener untuk tombol hapus
            removeButton.addEventListener('click', function() {
                row.remove();
                toggleRemoveButtons();
                calculateGrandTotal();
            });
        }
        
        // Fungsi untuk menampilkan/menyembunyikan tombol hapus
        function toggleRemoveButtons() {
            const itemRows = document.querySelectorAll('.item-row');
            const removeButtons = document.querySelectorAll('.remove-item');
            
            if (itemRows.length > 1) {
                removeButtons.forEach(button => {
                    button.style.display = 'block';
                });
            } else {
                removeButtons.forEach(button => {
                    button.style.display = 'none';
                });
            }
        }
        
        // Setup initial row
        document.querySelectorAll('.item-row').forEach(function(row) {
            setupEventListeners(row);
        });
        
        // Validasi form sebelum submit
        document.getElementById('poForm').addEventListener('submit', function(event) {
            const itemRows = document.querySelectorAll('.item-row');
            let valid = true;
            
            itemRows.forEach(function(row) {
                const itemSelect = row.querySelector('.item-select');
                const quantityInput = row.querySelector('.item-quantity');
                
                if (!itemSelect.value) {
                    valid = false;
                    itemSelect.classList.add('is-invalid');
                } else {
                    itemSelect.classList.remove('is-invalid');
                }
                
                if (!quantityInput.value || quantityInput.value < 1) {
                    valid = false;
                    quantityInput.classList.add('is-invalid');
                } else {
                    quantityInput.classList.remove('is-invalid');
                }
            });
            
            if (!valid) {
                event.preventDefault();
                alert('Silakan isi semua data item dengan benar');
            }
        });
    </script>
@endsection