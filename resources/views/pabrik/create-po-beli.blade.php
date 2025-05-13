@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Buat PO Pembelian Baru</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('pabrik.po-beli.store') }}" method="POST" id="poForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id">Pemasok</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                                        <option value="">Pilih Pemasok</option>
                                        @foreach($pemasok as $p)
                                            <option value="{{ $p->id_pemasok }}">{{ $p->nama_pemasok }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id">Karyawan</label>
                                    <select name="employee_id" id="employee_id" class="form-control" required>
                                        <option value="">Pilih Karyawan</option>
                                        @foreach($karyawan as $k)
                                            <option value="{{ $k->id_karyawan }}">{{ $k->nama_karyawan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>Detail Item</h6>
                                <div id="items-container">
                                    <div class="item-row row mb-2">
                                        <div class="col-md-4">
                                            <select name="items[0][item_id]" class="form-control item-select" required>
                                                <option value="">Pilih Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id_item }}" 
                                                            data-price="{{ $item->harga_per_item }}">
                                                        {{ $item->nama_item }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="items[0][quantity]" 
                                                   class="form-control quantity-input" 
                                                   placeholder="Jumlah" min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control price-input" 
                                                   placeholder="Harga" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control subtotal-input" 
                                                   placeholder="Subtotal" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success" id="add-item">
                                    <i class="fas fa-plus"></i> Tambah Item
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total Harga</label>
                                    <input type="text" id="total-price" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Simpan PO</button>
                                <a href="{{ route('pabrik.po-beli') }}" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let itemCount = 1;

    // Add new item row
    $('#add-item').click(function() {
        const newRow = $('.item-row:first').clone();
        newRow.find('select').attr('name', `items[${itemCount}][item_id]`);
        newRow.find('input[type="number"]').attr('name', `items[${itemCount}][quantity]`).val('');
        newRow.find('.price-input').val('');
        newRow.find('.subtotal-input').val('');
        $('#items-container').append(newRow);
        itemCount++;
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            calculateTotal();
        }
    });

    // Calculate subtotal when item or quantity changes
    $(document).on('change', '.item-select, .quantity-input', function() {
        const row = $(this).closest('.item-row');
        const price = row.find('.item-select option:selected').data('price') || 0;
        const quantity = row.find('.quantity-input').val() || 0;
        const subtotal = price * quantity;

        row.find('.price-input').val(formatRupiah(price));
        row.find('.subtotal-input').val(formatRupiah(subtotal));
        calculateTotal();
    });

    // Calculate total price
    function calculateTotal() {
        let total = 0;
        $('.subtotal-input').each(function() {
            const value = $(this).val().replace(/[^\d]/g, '');
            total += parseInt(value) || 0;
        });
        $('#total-price').val(formatRupiah(total));
    }

    // Format number to Rupiah
    function formatRupiah(number) {
        return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
});
</script>
@endpush
@endsection 