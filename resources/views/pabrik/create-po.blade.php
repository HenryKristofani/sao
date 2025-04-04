@extends('layouts.app')

@section('title', 'Buat PO Jual')

@section('content')
<div class="container mt-4">
    <h2>Buat PO Jual</h2>

    <form action="{{ route('po-jual.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama Customer</label>
            <input type="text" name="customer" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Total Harga</label>
            <input type="number" name="total_harga" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan PO</button>
        <a href="{{ route('po-jual.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
