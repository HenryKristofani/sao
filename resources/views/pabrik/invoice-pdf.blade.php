<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $nomor }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            text-decoration: underline;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 140px;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .total-section {
            margin-top: 20px;
            width: 100%;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 5px;
        }
        .total-label {
            width: 140px;
            text-align: right;
            padding-right: 10px;
            font-weight: bold;
        }
        .total-value {
            width: 120px;
            text-align: right;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .notes {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT. NAMA PERUSAHAAN</div>
        <div class="company-address">Jl. Contoh Alamat No. 123, Kota</div>
        <div>Telp: (021) 1234567 | Email: info@perusahaan.com</div>
    </div>
    
    <div class="document-title">INVOICE</div>
    
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Nomor Invoice</div>
            <div>: {{ $nomor }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal</div>
            <div>: {{ $tanggal }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nomor PO</div>
            <div>: {{ $poNumber }}</div>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Kepada</div>
            <div>: {{ $penjualan->pelanggan->nama_pelanggan }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Alamat</div>
            <div>: {{ $penjualan->pelanggan->alamat_pelanggan }}</div>
        </div>
        @if($penjualan->pelanggan->telepon_pelanggan)
        <div class="info-row">
            <div class="info-label">Telepon</div>
            <div>: {{ $penjualan->pelanggan->telepon_pelanggan }}</div>
        </div>
        @endif
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Item</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $detail->item->nama_item }}</td>
                <td>{{ $detail->jumlah_jual }}</td>
                <td style="text-align: right;">Rp {{ number_format($detail->harga_jual_satuan, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($detail->subtotal_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row">
            <div class="total-label">Subtotal:</div>
            <div class="total-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">PPN (11%):</div>
            <div class="total-value">Rp {{ number_format($ppn, 0, ',', '.') }}</div>
        </div>
        <div class="total-row grand-total">
            <div class="total-label">Total:</div>
            <div class="total-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
    </div>
    
    <div class="notes">
        <p><strong>Catatan:</strong></p>
        <ol>
            <li>Pembayaran dilakukan melalui transfer ke rekening PT. NAMA PERUSAHAAN - Bank XYZ No. Rek 1234567890</li>
            <li>Harap mencantumkan nomor invoice pada keterangan transfer</li>
            <li>Invoice ini sah tanpa tanda tangan dan cap perusahaan</li>
        </ol>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Disiapkan Oleh</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Disetujui Oleh</div>
        </div>
    </div>
</body>
</html>