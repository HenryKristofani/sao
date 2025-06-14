@extends('layouts.app')

@section('content')
<!-- Include Sidebar -->
@include('layouts.pabrik-navbar')

<div class="content" style="margin-top: 60px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Daftar Item</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Item</th>
                                    <th>Jenis</th>
                                    <th>Lokasi</th>
                                    <th>Jumlah</th>
                                    <th>Harga Per Item</th>
                                    <th>Masa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->id_item }}</td>
                                    <td>{{ $item->nama_item }}</td>
                                    <td>{{ $item->id_jenis }}</td>
                                    <td>{{ $item->id_lokasi_item }}</td>
                                    <td>{{ $item->jumlah_item }}</td>
                                    <td>Rp {{ number_format($item->harga_per_item, 0, ',', '.') }}</td>
                                    <td>{{ $item->masa_item }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data item</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection