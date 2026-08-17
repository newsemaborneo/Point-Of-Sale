<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
        }
        .page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 15px;
        }
        .label {
            width: 48mm;
            height: 25mm;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 5px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            box-sizing: border-box;
        }
        .product-name {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            margin-bottom: 2px;
        }
        .price {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }
        .barcode-img {
            max-width: 100%;
            height: 12px; /* sesuaikan proporsi */
        }
        .barcode-code {
            font-size: 9px;
            letter-spacing: 2px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .page {
                box-shadow: none;
                margin: 0;
                width: auto;
                min-height: auto;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        .print-btn {
            display: block;
            margin: 0 auto 20px auto;
            padding: 10px 20px;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            background-color: #4338ca;
        }
    </style>
</head>
<body>
    <button class="no-print print-btn" onclick="window.print()">Cetak Halaman Ini</button>

    <div class="page">
        @foreach($products as $product)
            @for($i = 0; $i < $copies; $i++)
                <div class="label">
                    <div class="product-name">{{ $product->name }}</div>
                    
                    @php
                        $code = $product->barcode ?: ($product->sku ?: '00000000');
                    @endphp
                    
                    <div style="text-align:center;">
                        <img class="barcode-img" src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($code, 'C128', 1.5, 30) }}" alt="barcode" />
                        <div class="barcode-code">{{ $code }}</div>
                    </div>
                    
                    <div class="price">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</div>
                </div>
            @endfor
        @endforeach
    </div>

    <script>
        window.onload = function() {
            // Optional: langsung trigger dialog print
            // window.print();
        }
    </script>
</body>
</html>
