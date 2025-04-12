@extends('layouts.app')

@section('title', 'Edit PO Jual')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Edit PO Jual</h3>

    <form action="{{ route('po-jual.update', $po->id) }}" method="POST">
        @csrf

        <div class="form-group">
    <label>Nomor PO</label>
    <p class="form-control-plaintext">{{ $po->nomor_po ?? '-' }}</p>
</div>


        <div class="mb-3">
            <label for="customer" class="form-label">Customer</label>
            <input type="text" name="customer" class="form-control" value="{{ $po->customer }}" required>
        </div>

        <div class="mb-3">
            <label for="total_harga" class="form-label">Total Harga</label>
            <input type="number" name="total_harga" class="form-control" value="{{ $po->total_harga }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('po-jual.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
