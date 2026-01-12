<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi King Gym</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #e8e517; color: black; }
    </style>
</head>
<body>
    <h2>Rekap Transaksi King Gym</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Member</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->member->name ?? 'Umum' }}</td>
                <td>Rp {{ number_format($t->total_amount) }}</td>
                <td>{{ strtoupper($t->payment_method) }}</td>
                <td>{{ $t->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>