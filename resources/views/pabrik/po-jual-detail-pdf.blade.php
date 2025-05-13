<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail PO Penjualan - {{ $poNumber }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        .header p {
            margin: 0;
            font-size: 12px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .company-address {
            margin: 5px 0;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            text-decoration: underline;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            display: grid;
            grid-template-columns: 150px auto;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 60px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .status-draft {
            background-color: #f0ad4e;
        }
        .status-approved {
            background-color: #5cb85c;
        }
        .status-canceled {
            background-color: #d9534f;
        }
        .status-amended {
            background-color: #5bc0de;
        }
        .status-completed {
            background-color: #0275d8;
        }
    </style>
</head>
<body>
    <div class="company-info">
        <p class="company-name">PT. NAMA PERUSAHAAN</p>
        <p class="company-address">Jl. Alamat Perusahaan No. 123</p>
        <p class="company-address">Telepon: (021) 123-4567 | Email: info@perusahaan.com</p>
    </div>

    <h1 class="doc-title">{{ $judul }}</h1>
    
    <div class="info-section">
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Nomor PO:</span>
                    <span>{{ $poNumber }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal PO:</span>
                    <span>{{ date('d F Y', strtotime($penjualan->tanggal_penjualan)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span>
                        @if($isApproved)
                            @if($penjualan->status == 'canceled')
                                <span class="status-badge status-canceled">Canceled</span>
                            @elseif($penjualan->status == 'amended')
                                <span class="status-badge status-amended">Amended</span>
                            @elseif($penjualan->status == 'approved')
                                <span class="status-badge status-approved">Approved</span>
                            @else
                                <span class="status-badge status-completed">Completed</span>
                            @endif
                        @else
                            <span class="status-badge status-draft">Draft</span>
                        @endif
                    </span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Pelanggan:</span>
                    <span>{{ $penjualan->pelanggan->nama_pelanggan }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Alamat:</span>
                    <span>{{ $penjualan->pelanggan->alamat_pelanggan ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">No Telepon:</span>
                    <span>{{ $penjualan->pelanggan->no_telepon ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Item</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detailPenjualan as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->item->nama_item }}</td>
                    <td>{{ $detail->jumlah_jual }}</td>
                    <td class="text-end">Rp {{ number_format($detail->harga_jual_satuan, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($detail->subtotal_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <th colspan="4" class="text-end">Total</th>
                <th class="text-end">Rp {{ number_format($penjualan->total_harga_penjualan, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak pada tanggal {{ $tanggal }} dan merupakan dokumen resmi perusahaan.</p>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Dibuat oleh</p>
            <p>{{ $penjualan->karyawan->nama_karyawan }}</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Disetujui oleh</p>
            <p>___________________</p>
        </div>
    </div>
</body>
</html>