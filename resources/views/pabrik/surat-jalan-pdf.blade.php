<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo {
            max-height: 70px;
            float: left;
        }
        .company-info {
            float: left;
            margin-left: 10px;
            font-size: 10px;
        }
        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 30px 0;
        }
        .details {
            width: 100%;
            margin-bottom: 20px;
        }
        .details td {
            vertical-align: top;
            padding: 3px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
        }
        .signature {
            width: 30%;
            float: left;
            text-align: center;
            margin-right: 5%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        .clear {
            clear: both;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 35%;
            opacity: 0.1;
            transform: rotate(-30deg);
            font-size: 100px;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo.png') }}" class="logo" alt="Logo">
        <div class="company-info">
            <h2>VEDENSIA INTI PERKASA</h2>
            <p>Office & Factory: Jl. Caringin Sari 8 Sendangan Klaten 57416. Phone: (0272) 325920, 089687278379 Fax (0272) 325929</p>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <table class="details" width="100%">
        <tr>
            <td width="15%">Pemesan</td>
            <td width="2%">:</td>
            <td width="40%">{{ $penjualan->pelanggan->nama_pelanggan }}</td>
            <td width="15%">No. PO</td>
            <td width="2%">:</td>
            <td>{{ $poNumber }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $penjualan->pelanggan->alamat_pelanggan ?? 'KANTOR PUSAT' }}</td>
            <td>Tgl. PO</td>
            <td>:</td>
            <td>{{ date('d-m-Y', strtotime($penjualan->tanggal_penjualan)) }}</td>
        </tr>
        <tr>
            <td>Alamat Kirim</td>
            <td>:</td>
            <td>{{ $penjualan->pelanggan->alamat_pengiriman ?? 'KANTOR PUSAT' }}</td>
            <td>Code PO</td>
            <td>:</td>
            <td>{{ substr($poNumber, 0, 10) }}</td>
        </tr>
    </table>
    
    <div class="title" style="text-align: center; font-size: 24px; font-weight: bold;">SURAT JALAN</div>
    
    <table class="details" width="100%">
        <tr>
            <td width="15%">No.</td>
            <td width="2%">:</td>
            <td width="40%">{{ $nomor }}</td>
            <td width="15%">No. Kend.</td>
            <td width="2%">:</td>
            <td></td>
        </tr>
        <tr>
            <td>TGL</td>
            <td>:</td>
            <td>{{ date('d-m-Y') }}</td>
            <td>Driver</td>
            <td>:</td>
            <td></td>
        </tr>
    </table>
    
    <p>Harap diterima dengan baik dan benar barang-barang anda seperti di bawah ini.</p>
    
    <table class="table">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="40%">NOMOR/NAMA BARANG</th>
                <th width="20%">UKURAN</th>
                <th width="15%">JUMLAH PCS</th>
                <th width="20%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $detail->item->nama_item }}</td>
                <td>{{ $detail->item->ukuran ?? '-' }}</td>
                <td>{{ $detail->jumlah_jual }}</td>
                <td>{{ $detail->keterangan ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p>Barang tersebut di atas telah di terima dengan baik dan benar sesuai pesanan.</p>
    
    <div class="footer">
        <div class="signature">
            <p>Pembeli</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature">
            <p>Adv. Gudang</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature">
            <p>Driver</p>
            <div class="signature-line"></div>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>