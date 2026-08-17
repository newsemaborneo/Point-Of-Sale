<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode - {{ $product->name }}</title>
    <style>
        body { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .label { text-align: center; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .product-name { font-weight: bold; margin-bottom: 10px; font-size: 1.2rem; }
        .code { margin-top: 10px; font-family: monospace; font-size: 1rem; letter-spacing: 2px; }
        .price { font-size: 1.5rem; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="label">
        <div class="product-name">{{ $product->name }}</div>
        <div>{!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($code, 'C128', 2, 60) !!}</div>
        <div class="code">{{ $code }}</div>
        <div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
    </div>
    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
