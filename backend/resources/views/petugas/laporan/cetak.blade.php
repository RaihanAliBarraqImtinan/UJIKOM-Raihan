<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Peminjaman #{{ $peminjaman->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .receipt {
            width: 300px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; font-size: 12px; }
        td { padding: 3px 0; vertical-align: top; }
        .btn-print {
            display: block;
            width: 100%;
            padding: 8px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            margin-bottom: 15px;
            font-weight: bold;
        }
        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; border: none; width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="receipt">
    <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>

    <div class="text-center">
        <h3 style="margin: 0;">LABORATORIUM SMK</h3>
        <p style="font-size: 11px; margin: 5px 0;">Bukti Peminjaman Alat</p>
    </div>

    <div class="line"></div>

    <table style="font-size: 11px;">
        <tr>
            <td>No. Transaksi</td>
            <td class="text-right">#TRX-{{ $peminjaman->id }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td class="text-right">{{ $peminjaman->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Peminjam</td>
            <td class="text-right">{{ $peminjaman->user->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        <thead>
            <tr style="font-size: 11px;">
                <th style="text-align: left;">ALAT</th>
                <th style="text-align: right;">QTY</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peminjaman->detailPinjams as $detail)
                <tr>
                    <td>{{ $detail->alat->nama_alat ?? 'Alat' }}</td>
                    <td class="text-right">{{ $detail->jumlah }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table style="font-size: 11px;">
        <tr>
            <td>STATUS:</td>
            <td class="text-right"><strong>{{ strtoupper($peminjaman->status) }}</strong></td>
        </tr>
        @if(optional($peminjaman->pengembalian)->denda)
        <tr>
            <td>DENDA:</td>
            <td class="text-right"><strong>Rp {{ number_format($peminjaman->pengembalian->denda, 0, ',', '.') }}</strong></td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    <div class="text-center" style="font-size: 10px; margin-top: 15px;">
        <p>Harap jaga alat dengan baik.<br>Terima kasih!</p>
    </div>
</div>

</body>
</html>