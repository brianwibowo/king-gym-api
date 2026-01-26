<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #cccccc;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .section-title {
            background-color: #ffff00;
            font-weight: bold;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="header">LAPORAN TAHUNAN {{ $year }} - KING FITNESS SEMARANG</div>

    <!-- MEMBERSHIP SALES -->
    <table>
        <tr>
            <td colspan="3" class="section-title">PENJUALAN MEMBERSHIP (TOTAL SETAHUN)</td>
        </tr>
        <tr>
            <th>Nama Paket</th>
            <th>Qty Terjual</th>
            <th>Total Pendapatan</th>
        </tr>
        @forelse($sales['MEMBERSHIP'] as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">- Tidak ada data -</td>
            </tr>
        @endforelse
    </table>

    <!-- PRODUCT SALES -->
    <table>
        <tr>
            <td colspan="3" class="section-title">PENJUALAN PRODUK &amp; MINUMAN (TOTAL SETAHUN)</td>
        </tr>
        <tr>
            <th>Nama Produk</th>
            <th>Qty Terjual</th>
            <th>Total Pendapatan</th>
        </tr>
        @forelse($sales['PENJUALAN (PRODUK)'] as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">- Tidak ada data -</td>
            </tr>
        @endforelse
    </table>

    <!-- SUMMARY -->
    <table>
        <tr>
            <td colspan="2" class="section-title">RINGKASAN TAHUN {{ $year }}</td>
        </tr>
        <tr>
            <td>Total Pemasukan (Gross)</td>
            <td class="text-right bold">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Pengeluaran</td>
            <td class="text-right bold" style="color: red;">- Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #ffff99;">
            <td class="bold">NET PROFIT (BERSIH)</td>
            <td class="text-right bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
        </tr>
    </table>
</body>

</html>