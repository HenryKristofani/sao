@extends('layouts.app')

@section('title', 'PO Jual')

@section('content')
<div class="container mt-4">
    <h3>Purchase Order Jual</h3>

    <a href="{{ route('po-jual.create') }}" class="btn btn-primary mb-3">Buat PO</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nomor PO</th>
                <th>Customer</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Amendment</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Drafts --}}
            @foreach ($drafts as $po)
                <tr>
                    <td>{{ $po->nomor_po }}</td>
                    <td>{{ $po->customer }}</td>
                    <td>Rp{{ number_format($po->total_harga, 0, ',', '.') }}</td>
                    <td><span class="badge bg-secondary">Draft</span></td>
                    <td>
                        @if($po->is_amendment)
                            <span class="badge bg-warning">Yes</span>
                        @else
                            <span class="badge bg-success">No</span>
                        @endif
                    </td>
                    <td>
                        {{-- Edit draft --}}
                        <a href="{{ route('po-jual.edit', $po->id) }}" class="btn btn-sm btn-info">Edit</a>

                        {{-- Approve --}}
                        <form action="{{ route('po-jual.approve', $po->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>

                        {{-- Cancel hanya jika bukan hasil amend --}}
                        @if (!$po->is_amendment)
                            <form action="{{ route('po-jual.cancel', $po->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach

            {{-- Final PO (Approved, Amended, Canceled) --}}
            @foreach ($poList as $po)
                <tr>
                    <td>
                        @if($po->nomor_po)
                            {{ $po->nomor_po }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $po->customer }}</td>
                    <td>Rp{{ number_format($po->total_harga, 0, ',', '.') }}</td>
                    <td>
                        @if ($po->status === 'Approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif ($po->status === 'Amended')
                            <span class="badge bg-warning text-dark">Amended</span>
                        @elseif ($po->status === 'Canceled')
                            <span class="badge bg-danger">Canceled</span>
                        @endif
                    </td>
                    <td><span class="badge bg-light text-dark">-</span></td>
                    <td>
                        {{-- Edit hanya kalau Approved --}}
                        @if ($po->status === 'Approved')
                            <a href="{{ route('po-jual.edit', $po->id) }}" class="btn btn-sm btn-info">Edit</a>
                        @endif

                        {{-- Cancel hanya kalau Approved --}}
                        @if ($po->status === 'Approved')
                            <form action="{{ route('po-jual.cancel', $po->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
