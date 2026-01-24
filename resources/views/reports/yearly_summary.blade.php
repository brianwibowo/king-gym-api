<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
            font-size: 12px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .month-col {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }
    </style>
</head>

<body>
    <div style="text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 20px;">
        Laporan Tahunan King Fitness Gym<br>
        Tahun: {{ $year }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Total Transaksi (Trx)</th>
                <th>Pemasukan Membership</th>
                <th>Pemasukan Produk</th>
                <th>Total Pemasukan</th>
                <th>Total Pengeluaran</th>
                <th>Net Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="month-col">{{ $row['month_name'] }}</td>
                    <td>{{ number_format($row['trx_count'], 0, ',', '.') }}</td>
                    <td>{{ number_format($row['income_membership'], 0, ',', '.') }}</td>
                    <td>{{ number_format($row['income_product'], 0, ',', '.') }}</td>
                    <td style="font-weight: bold;">{{ number_format($row['total_income'], 0, ',', '.') }}</td>
                    <td style="color: red;">{{ number_format($row['total_expense'], 0, ',', '.') }}</td>
                    <td style="font-weight: bold; color: {{ $row['net_profit'] >= 0 ? 'green' : 'red' }};">
                        {{ number_format($row['net_profit'], 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td class="month-col">TOTAL TAHUN INI</td>
                <td>{{ number_format(collect($data)->sum('trx_count'), 0, ',', '.') }}</td>
                <td>{{ number_format(collect($data)->sum('income_membership'), 0, ',', '.') }}</td>
                <td>{{ number_format(collect($data)->sum('income_product'), 0, ',', '.') }}</td>
                <td>{{ number_format(collect($data)->sum('total_income'), 0, ',', '.') }}</td>
                <td>{{ number_format(collect($data)->sum('total_expense'), 0, ',', '.') }}</td>
                <td>{{ number_format(collect($data)->sum('net_profit'), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>