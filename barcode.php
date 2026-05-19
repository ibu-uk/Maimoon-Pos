<?php
require_once __DIR__ . '/includes/config.php';
require_login();

$db = db();
$pid = (int)($_GET['product_id'] ?? 0);

if ($pid) {
    $p = $db->prepare("SELECT * FROM products WHERE id = ?");
    $p->execute([$pid]);
    $product = $p->fetch();
}

if (!$product) {
    die('Product not found');
}

$barcode = $product['barcode'] ?: $product['sku'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Barcode - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #fff; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .barcode-card {
            border: 2px solid #000;
            padding: 20px;
            text-align: center;
            width: 300px;
            background: #fff;
        }
        .barcode-card .product-name { font-size: 16px; font-weight: bold; margin-bottom: 10px; line-height: 1.3; }
        .barcode-card .price { font-size: 28px; font-weight: bold; margin-bottom: 15px; color: #000; }
        .barcode-card .barcode { font-size: 32px; font-family: 'Courier New', monospace; font-weight: bold; letter-spacing: 3px; margin-bottom: 8px; }
        .barcode-card .sku { font-size: 14px; color: #444; }
        .barcode-card .border-bottom { border-top: 2px solid #000; margin-top: 15px; padding-top: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Print</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; margin-left: 10px;">✕ Close</button>
    </div>
    
    <div class="barcode-card">
        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
        <div class="price"><?= fmt_money($product['retail_price']) ?></div>
        <img src="https://barcodeapi.org/api/128/<?= htmlspecialchars($barcode) ?>" alt="Barcode" style="height: 60px; margin: 10px 0;">
        <div class="sku"><?= htmlspecialchars($barcode) ?></div>
        <div class="border-bottom"></div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
