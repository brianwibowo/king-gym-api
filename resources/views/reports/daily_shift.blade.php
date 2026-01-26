<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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

        /* Excel Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #cccccc;
            font-weight: bold;
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
        }

        td {
            border: 1px solid #000000;
            padding: 5px;
        }

        .no-border {
            border: none !important;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        .header-date {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-align: left;
            background-color: #ffff00;
            padding: 5px;
            border: 1px solid #000;
        }

        .subsection-title {
            font-weight: bold;
            background-color: #e0e0e0;
            border: 1px solid #000;
        }

        .empty-row {
            height: 10px;
        }
    </style>
</head>

<body>

    <!-- MAIN HEADER -->
    <table>
        <tr>
            <td colspan="4" class="no-border header-title">LAPORAN HARIAN KING FITNESS SEMARANG</td>
        </tr>
        <tr>
            <td colspan="4" class="no-border header-date">TANGGAL:
                {{ strtoupper(\Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y')) }}
            </td>
        </tr>
    </table>

    <!-- ================= SHIFT PAGI ================= -->
    <table>
        <tr>
            <td colspan="4" class="section-title">SHIFT PAGI (06:00 - 14:00)</td>
        </tr>
    </table>

    <!-- 1. MEMBERSHIP PAGI -->
    <table>
        <thead>
            <tr>
                <th colspan="4" class="subsection-title" style="text-align: left;">MEMBERSHIP</th>
            </tr>
            <tr>
                <th style="width: 200px;">PENJUALAN</th>
                <th style="width: 80px;">Jumlah</th>
                <th style="width: 120px;">Harga</th>
                <th style="width: 120px;">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMembPagi = 0; @endphp
            @forelse($pagi['sales']['MEMBERSHIP'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
                @php $totalMembPagi += $item['total']; @endphp
            @empty
                <tr>
                    <td colspan="4" class="text-center">- Tidak ada penjualan membership -</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="3" class="text-right">Total Membership</td>
                <td class="text-right">Rp {{ number_format($totalMembPagi, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. PENJUALAN PRODUK PAGI -->
    <table>
        <thead>
            <tr>
                <th colspan="4" class="subsection-title" style="text-align: left;">PENJUALAN (PRODUK &amp; MINUMAN)</th>
            </tr>
            <tr>
                <th>PENJUALAN</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalProdPagi = 0; @endphp
            @forelse($pagi['sales']['PENJUALAN (PRODUK)'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
                @php $totalProdPagi += $item['total']; @endphp
            @empty
                <tr>
                    <td colspan="4" class="text-center">- Tidak ada penjualan produk -</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="3" class="text-right">Total Penjualan</td>
                <td class="text-right">Rp {{ number_format($totalProdPagi, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. SUMMARY PENDAPATAN PAGI -->
    <table>
        <thead>
            <tr>
                <th colspan="2" class="subsection-title" style="text-align: left;">PENDAPATAN SHIFT PAGI</th>
            </tr>
        </thead>
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
            <tr class="bold" style="background-color: #dce6f1;">
                <td>Total Pendapatan Pagi</td>
                <td class="text-right">Rp {{ number_format($pagi['incomeTotal'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. PENGELUARAN PAGI -->
    <table>
        <thead>
            <tr>
                <th colspan="3" class="subsection-title" style="text-align: left;">PENGELUARAN SHIFT PAGI</th>
            </tr>
            <tr>
                <th style="width: 250px;">Deskripsi</th>
                <th style="width: 120px;">Tanggal</th>
                <th style="width: 120px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagi['expenses'] as $exp)
                <tr>
                    <td>{{ $exp->description }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($exp->created_at ?? $exp->date)->format('d F Y') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">- Tidak ada pengeluaran -</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="2" class="text-right">Total Pengeluaran</td>
                <td class="text-right">Rp {{ number_format($pagi['expenseTotal'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- NET PROFIT PAGI -->
    <table>
        <tr class="bold" style="background-color: #ffff99; font-size: 12px;">
            <td colspan="3" class="text-right">JUMLAH AKHIR (BERSIH) SHIFT PAGI:</td>
            <td class="text-right">Rp {{ number_format($pagi['netProfit'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="empty-row"></div>
    <div class="empty-row"></div>

    <!-- ================= SHIFT SORE ================= -->
    <table>
        <tr>
            <td colspan="4" class="section-title">SHIFT SORE (14:00 - Tutup)</td>
        </tr>
    </table>

    <!-- 1. MEMBERSHIP SORE -->
    <table>
        <thead>
            <tr>
                <th colspan="4" class="subsection-title" style="text-align: left;">MEMBERSHIP</th>
            </tr>
            <tr>
                <th>PENJUALAN</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMembSore = 0; @endphp
            @forelse($sore['sales']['MEMBERSHIP'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
                @php $totalMembSore += $item['total']; @endphp
            @empty
                <tr>
                    <td colspan="4" class="text-center">- Tidak ada penjualan membership -</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="3" class="text-right">Total Membership</td>
                <td class="text-right">Rp {{ number_format($totalMembSore, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. PENJUALAN PRODUK SORE -->
    <table>
        <thead>
            <tr>
                <th colspan="4" class="subsection-title" style="text-align: left;">PENJUALAN (PRODUK &amp; MINUMAN)</th>
            </tr>
            <tr>
                <th>PENJUALAN</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalProdSore = 0; @endphp
            @forelse($sore['sales']['PENJUALAN (PRODUK)'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
                @php $totalProdSore += $item['total']; @endphp
            @empty
                <tr>
                    <td colspan="4" class="text-center">- Tidak ada penjualan produk -</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="3" class="text-right">Total Penjualan</td>
                <td class="text-right">Rp {{ number_format($totalProdSore, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. SUMMARY PENDAPATAN SORE -->
    <table>
        <thead>
            <tr>
                <th colspan="2" class="subsection-title" style="text-align: left;">PENDAPATAN SHIFT SORE</th>
            </tr>
        </thead>
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
            <tr class="bold" style="background-color: #dce6f1;">
                <td>Total Pendapatan Sore</td>
                <td class="text-right">Rp {{ number_format($sore['incomeTotal'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. PENGELUARAN SORE -->
    <table>
        <thead>
            <tr>
                <th colspan="3" class="subsection-title" style="text-align: left;">PENGELUARAN SHIFT SORE</th>
            </tr>
            <tr>
                <th>Deskripsi</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sore['expenses'] as $exp)
                <tr>
                    <td>{{ $exp->description }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($exp->created_at ?? $exp->date)->format('d F Y') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">- Tidak ada pengeluaran -</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="2" class="text-right">Total Pengeluaran</td>
                <td class="text-right">Rp {{ number_format($sore['expenseTotal'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- NET PROFIT SORE -->
    <table>
        <tr class="bold" style="background-color: #ffff99; font-size: 12px;">
            <td colspan="3" class="text-right">JUMLAH AKHIR (BERSIH) SHIFT SORE:</td>
            <td class="text-right">Rp {{ number_format($sore['netProfit'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="empty-row"></div>

    <!-- GRAND TOTAL -->
    <table>
        <tr class="bold" style="background-color: #000; color: #fff; font-size: 14px;">
            <td colspan="3" class="text-center">TOTAL BERSIH HARI INI (PAGI + SORE)</td>
            <td class="text-right">Rp {{ number_format($pagi['netProfit'] + $sore['netProfit'], 0, ',', '.') }}</td>
        </tr>
    </table>

</body>

</html>