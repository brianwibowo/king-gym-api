<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header-title {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin-bottom: 5px;
        }

        .header-date {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .section-header {
            font-weight: bold;
            font-size: 13px;
            margin-top: 15px;
            margin-bottom: 5px;
            text-decoration: underline;
        }

        .sub-header {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .income-table {
            width: 50%;
        }

        .expense-table {
            width: 100%;
        }

        .final-row {
            font-size: 13px;
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .shift-block {
            margin-bottom: 30px;
            border-bottom: 2px dashed #000;
            padding-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="header-title">LAPORAN HARIAN KING FITNESS SEMARANG</div>
    <div class="header-date">TANGGAL: {{ strtoupper($date) }}</div>

    <!-- SHIFT PAGI -->
    <div class="shift-block">
        <div class="section-header">SHIFT PAGI</div>

        @if(isset($pagi['sales']['MEMBERSHIP']))
            <div class="sub-header">MEMBERSHIP</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">PENJUALAN</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMemb = 0; @endphp
                    @foreach($pagi['sales']['MEMBERSHIP'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['qty'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                        @php $totalMemb += $item['total']; @endphp
                    @endforeach
                    <tr class="bold">
                        <td>Jumlah</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">Rp {{ number_format($totalMemb, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if(isset($pagi['sales']['PENJUALAN (PRODUK)']))
            <div class="sub-header">PRODUK / LAINNYA</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">PENJUALAN</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalProd = 0; @endphp
                    @foreach($pagi['sales']['PENJUALAN (PRODUK)'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['qty'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                        @php $totalProd += $item['total']; @endphp
                    @endforeach
                    <tr class="bold">
                        <td>Jumlah</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">Rp {{ number_format($totalProd, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="sub-header">PENDAPATAN</div>
        <table class="income-table">
            <tbody>
                <tr>
                    <td>Cash</td>
                    <td class="text-right">Rp {{ number_format($pagi['income']['CASH'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>QRIS</td>
                    <td class="text-right">Rp {{ number_format($pagi['income']['QRIS'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Transfer</td>
                    <td class="text-right">Rp {{ number_format($pagi['income']['TRANSFER'], 0, ',', '.') }}</td>
                </tr>
                <tr class="bold">
                    <td>Total Pendapatan</td>
                    <td class="text-right">Rp {{ number_format($pagi['incomeTotal'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="sub-header">PENGELUARAN</div>
        <table class="expense-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Deskripsi</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagi['expenses'] as $exp)
                    <tr>
                        <td>{{ $exp->description }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($exp->date)->format('d F Y') }}</td>
                        <td class="text-right">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="bold">
                    <td colspan="2">Total Pengeluaran</td>
                    <td class="text-right">Rp {{ number_format($pagi['expenseTotal'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 10px; font-weight: bold; font-size: 14px;">
            JUMLAH AKHIR SHIFT PAGI: Rp {{ number_format($pagi['netProfit'], 0, ',', '.') }}
        </div>
    </div>

    <!-- SHIFT SORE -->
    <div class="shift-block" style="border-bottom: none;">
        <div class="section-header">SHIFT SORE</div>

        @if(isset($sore['sales']['MEMBERSHIP']))
            <div class="sub-header">MEMBERSHIP</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">PENJUALAN</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMembSore = 0; @endphp
                    @foreach($sore['sales']['MEMBERSHIP'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['qty'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                        @php $totalMembSore += $item['total']; @endphp
                    @endforeach
                    <tr class="bold">
                        <td>Jumlah</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">Rp {{ number_format($totalMembSore, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if(isset($sore['sales']['PENJUALAN (PRODUK)']))
            <div class="sub-header">PRODUK / LAINNYA</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">PENJUALAN</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalProdSore = 0; @endphp
                    @foreach($sore['sales']['PENJUALAN (PRODUK)'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['qty'] }}</td>
                            <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                        @php $totalProdSore += $item['total']; @endphp
                    @endforeach
                    <tr class="bold">
                        <td>Jumlah</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">Rp {{ number_format($totalProdSore, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="sub-header">PENDAPATAN</div>
        <table class="income-table">
            <tbody>
                <tr>
                    <td>Cash</td>
                    <td class="text-right">Rp {{ number_format($sore['income']['CASH'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>QRIS</td>
                    <td class="text-right">Rp {{ number_format($sore['income']['QRIS'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Transfer</td>
                    <td class="text-right">Rp {{ number_format($sore['income']['TRANSFER'], 0, ',', '.') }}</td>
                </tr>
                <tr class="bold">
                    <td>Total Pendapatan</td>
                    <td class="text-right">Rp {{ number_format($sore['incomeTotal'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="sub-header">PENGELUARAN</div>
        <table class="expense-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Deskripsi</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sore['expenses'] as $exp)
                    <tr>
                        <td>{{ $exp->description }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($exp->date)->format('d F Y') }}</td>
                        <td class="text-right">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="bold">
                    <td colspan="2">Total Pengeluaran</td>
                    <td class="text-right">Rp {{ number_format($sore['expenseTotal'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 10px; font-weight: bold; font-size: 14px;">
            JUMLAH AKHIR SHIFT SORE: Rp {{ number_format($sore['netProfit'], 0, ',', '.') }}
        </div>
    </div>

    <!-- GRAND TOTAL -->
    <div style="margin-top: 30px; border-top: 2px solid #000; padding-top: 10px;">
        <div style="font-size: 16px; font-weight: bold; text-align: center;">
            TOTAL BERSIH HARI INI: Rp {{ number_format($netProfit, 0, ',', '.') }}
        </div>
    </div>

</body>

</html>