@extends('layouts.app')

@section('content')
@include('layouts.SidebarPabrik')
<div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="card-title">Edit PO Pembelian</h5>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('pabrik.po-beli.update', $pembelian->id_pembelian) }}" method="POST" id="poForm">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_id">Pemasok</label>
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="">Pilih Pemasok</option>
                                @foreach($pemasok as $p)
                                    <option value="{{ $p->id_pemasok }}" 
                                            {{ $pembelian->id_pemasok == $p->id_pemasok ? 'selected' : '' }}>
                                        {{ $p->nama_pemasok }}
                                    </option>
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
                                    <option value="{{ $k->id_karyawan }}"
                                            {{ $pembelian->id_karyawan == $k->id_karyawan ? 'selected' : '' }}>
                                        {{ $k->nama_karyawan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6>Detail Item</h6>
                        <div id="items-container">
                            @foreach($detailPembelian as $index => $detail)
                                <div class="item-row row mb-2">
                                    <div class="col-md-4">
                                        <select name="items[{{ $index }}][item_id]" class="form-control item-select" required>
                                            <option value="">Pilih Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id_item }}" 
                                                        data-price="{{ $item->harga_per_item }}"
                                                        {{ $detail->id_item == $item->id_item ? 'selected' : '' }}>
                                                    {{ $item->nama_item }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control quantity-input" 
                                               placeholder="Jumlah" min="1" required
                                               value="{{ $detail->jumlah_beli }}">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control price-input" 
                                               placeholder="Harga" readonly
                                               value="{{ number_format($detail->harga_beli_satuan, 0, ',', '.') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control subtotal-input" 
                                               placeholder="Subtotal" readonly
                                               value="{{ number_format($detail->subtotal_harga, 0, ',', '.') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
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
                            <input type="text" id="total-price" class="form-control" readonly
                                   value="{{ number_format($pembelian->total_harga_pembelian, 0, ',', '.') }}">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('pabrik.po-beli.show', $pembelian->id_pembelian) }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let itemCount = {{ count($detailPembelian) }};

    // Add new item row
    $('#add-item').click(function() {
        const newRow = $('.item-row:first').clone();
        newRow.find('select').attr('name', `items[${itemCount}][item_id]`).val('');
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

    // Initialize calculations for existing items
    $('.item-row').each(function() {
        const row = $(this);
        const price = row.find('.item-select option:selected').data('price') || 0;
        const quantity = row.find('.quantity-input').val() || 0;
        const subtotal = price * quantity;

        row.find('.price-input').val(formatRupiah(price));
        row.find('.subtotal-input').val(formatRupiah(subtotal));
    });
});
</script>
@endpush
@endsection 