<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
        }
        .report-info {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .report-info p {
            margin: 0;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary {
            margin-top: 20px;
            text-align: right;
        }
        .summary p {
            margin: 5px 0;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Penjualan</h1>
            <p>Periode: {{ \Carbon\Carbon::parse($request->date_from ?? now()->startOfMonth())->format('d M Y') }} - {{ \Carbon\Carbon::parse($request->date_to ?? now()->endOfMonth())->format('d M Y') }}</p>
            <p>Cabang:
                @if (!$isAdminOrSupervisor && $userBranchName)
                    {{ $userBranchName }}
                @elseif ($selectedBranchId)
                    {{ $branches->firstWhere('id', $selectedBranchId)->name ?? 'Semua Cabang' }}
                @else
                    Semua Cabang
                @endif
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    @if ($isAdminOrSupervisor || $branches->isNotEmpty())
                        <th>Cabang</th>
                    @endif
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        @if ($isAdminOrSupervisor || $branches->isNotEmpty())
                            <td>{{ $sale->branch->name ?? '-' }}</td>
                        @endif
                        <td>{{ $sale->customer->name ?? 'Umum' }}</td>
                        <td>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                        <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                        <td>{{ ucfirst($sale->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($isAdminOrSupervisor || $branches->isNotEmpty()) ? 6 : 5 }}" style="text-align: center;">Tidak ada data penjualan untuk periode ini.</td>
                    </tr>
                @endforelse 
            </tbody>
        </table>

        <div class="summary">
            <p>Total Pendapatan: Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            <p>Total Transaksi: {{ $totalTransactions }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>