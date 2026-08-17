<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan #{{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
            color: #000;
        }
        .receipt-container {
            width: 100%;
            max-width: 80mm; /* Optimal untuk printer thermal 80mm */
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .border-t { border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; }
        .flex { display: flex; justify-content: space-between; }
        .item-row { margin-bottom: 6px; }
        .item-name { display: block; margin-bottom: 2px; }
        .item-details { display: flex; justify-content: space-between; padding-left: 10px; }

        .no-print {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-print { background: #4f46e5; color: white; }
        .btn-print:hover { background: #4338ca; }
        .btn-back { background: #e5e7eb; color: #374151; }
        .btn-back:hover { background: #d1d5db; }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
            }
            .receipt-container {
                max-width: 100%;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="text-center mb-4">
            <div class="font-bold" style="font-size: 18px;">{{ config('app.name', 'POS App') }}</div>
            <div>Jl. Contoh No. 123, Kota Anda</div>
            <div>Telp: (021) 12345678</div>
        </div>

        <div class="mb-2">
            <div class="flex"><span>No:</span> <span>{{ $sale->invoice_number }}</span></div>
            <div class="flex"><span>Tgl:</span> <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="flex"><span>Ksr:</span> <span>{{ $sale->user->name ?? 'N/A' }}</span></div>
            <div class="flex"><span>Plg:</span> <span>{{ $sale->customer->name ?? 'Umum' }}</span></div>
        </div>

        <div class="border-t">
            @foreach ($sale->items as $item)
                <div class="item-row">
                    <div class="item-name">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                    <div class="item-details">
                        <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t mb-2">
            <div class="flex"><span>Subtotal</span> <span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
            @if($sale->discount_total > 0)
                <div class="flex"><span>Diskon</span> <span>-{{ number_format($sale->discount_total, 0, ',', '.') }}</span></div>
            @endif
            @if($sale->tax_total > 0)
                <div class="flex"><span>Pajak</span> <span>{{ number_format($sale->tax_total, 0, ',', '.') }}</span></div>
            @endif

            <div class="flex font-bold mt-2" style="font-size: 14px;">
                <span>TOTAL</span>
                <span>{{ number_format($sale->grand_total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="border-t">
            <div class="flex">
                <span>{{ ucfirst($sale->payment_method ?? 'Tunai') }}</span>
                <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex">
                <span>Kembali</span>
                <span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="text-center mt-4" style="font-size: 11px;">
            <div class="mb-1">*** TERIMA KASIH ***</div>
            <div>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">Cetak Struk</button>
        <a href="{{ url()->previous() == url()->current() ? route('transactions.index') : url()->previous() }}" class="btn btn-back">Kembali</a>
    </div>

    <script>
        window.onload = function () {
            setTimeout(() => {
                try {
                    window.print();
                } catch (error) {
                    console.warn('Print failed:', error);
                }
            }, 400);
        };

        window.onafterprint = function () {
            setTimeout(() => {
                if (window.opener) {
                    window.close();
                }
            }, 300);
        };
    </script>
</body>
</html>
