@extends('layouts.app')

@section('title', 'Buat PO Jual')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Buat PO Jual</h3>

    <form action="{{ route('po-jual.store') }}" method="POST">
        @csrf




        <div class="mb-3">
            <label for="customer" class="form-label">Customer</label>
            <input type="text" name="customer" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="total_harga" class="form-label">Total Harga</label>
            <input type="number" name="total_harga" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('po-jual.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
