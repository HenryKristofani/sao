@extends('layouts.app')

@section('title', 'Dashboard Pabrik')

@section('content')
<div class="container">
    <h1 class="text-center mb-4">Dashboard Pabrik</h1>

    <div class="row">
        <!-- PO Jual -->
        <div class="col-md-3">
            <div class="card text-bg-primary shadow">
                <div class="card-body text-center">
                    <i class="fa-solid fa-shopping-cart fa-3x"></i>
                    <a href="{{ route('po-jual.index') }}" class="btn btn-primary">PO Jual</a>
                    <p class="mb-0">10 Pesanan</p>
                </div>
            </div>
        </div>

        <!-- PO Beli -->
        <div class="col-md-3">
            <div class="card text-bg-success shadow">
                <div class="card-body text-center">
                    <i class="fa-solid fa-truck fa-3x"></i>
                    <h4 class="mt-2">PO Beli</h4>
                    <p class="mb-0">5 Pesanan</p>
                </div>
            </div>
        </div>

        <!-- Scheduler -->
        <div class="col-md-3">
            <div class="card text-bg-warning shadow">
                <div class="card-body text-center">
                    <i class="fa-solid fa-calendar-check fa-3x"></i>
                    <h4 class="mt-2">Scheduler</h4>
                    <p class="mb-0">3 Jadwal</p>
                </div>
            </div>
        </div>

        <!-- Inventory -->
        <div class="col-md-3">
            <div class="card text-bg-danger shadow">
                <div class="card-body text-center">
                    <i class="fa-solid fa-box fa-3x"></i>
                    <h4 class="mt-2">Inventory</h4>
                    <p class="mb-0">150 Item</p>
                </div>
            </div>
        </div>

        <!-- Rekap Penjualan -->
        <div class="col-md-3">
            <div class="card text-bg-warning shadow">
                <div class="card-body text-center">
                    <i class="fa-solid fa-box fa-3x"></i>
                    <h4 class="mt-2">Rekap Penjualan</h4>
                    <p class="mb-0">Lihat rekapan per bulan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
@endsection
