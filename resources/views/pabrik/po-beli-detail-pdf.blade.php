<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h4 {
            margin: 0 0 10px 0;
            padding: 0;
            border-bottom: 1px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 50px;
        }
        .signature {
            float: right;
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>DETAIL PURCHASE ORDER</h2>
        <p>No: {{ $poNumber }}</p>
        <p>Tanggal: {{ $tanggal }}</p>
    </div>

    <div class="info-section">
        <h4>Informasi Pemasok</h4>
        <table>
            <tr>
                <th width="30%">Nama Pemasok</th>
                <td>{{ $pembelian->pemasok->nama_pemasok }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $pembelian->pemasok->alamat_pemasok }}</td>
            </tr>
            <tr>
                <th>Telepon</th>
                <td>{{ $pembelian->pemasok->telepon_pemasok }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $pembelian->pemasok->email_pemasok }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h4>Detail Item</h4>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Item</th>
                    <th width="15%">Jumlah</th>
                    <th width="20%">Harga Satuan</th>
                    <th width="20%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detailPembelian as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $detail->item->nama_item }}</td>
                        <td class="text-center">{{ $detail->jumlah_beli }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga_beli_satuan, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->subtotal_harga, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th class="text-right">Rp {{ number_format($pembelian->total_harga_pembelian, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <div class="signature">
            <p>Dibuat oleh,</p>
            <div class="signature-line">
                {{ $pembelian->karyawan->nama_karyawan }}
            </div>
        </div>
    </div>
</body>
</html> 