@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-left: 230px; margin-top: 60px;">
        <h4 class="fw-bold mb-4">PO Jual</h4>

        <div class="d-flex align-items-center mb-4 gap-2 flex-wrap">
            <a href="{{ route('pabrik.po-jual.create') }}" class="btn btn-success px-4">Buat PO</a>
            <button class="btn btn-light border">Approve</button>
            <button class="btn btn-light border">Cancel</button>
            <button class="btn btn-light border d-flex align-items-center">
                <span class="me-2">📄</span> CSV
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead style="background-color: #f8f6f4;">
                    <tr>
                        <th>Sale ID</th>
                        <th>Client ID</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Placeholder data --}}
                    <tr>
                        <td colspan="5" class="text-center text-muted">No sales orders yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
