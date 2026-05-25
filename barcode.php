<?php
require_once __DIR__ . '/includes/config.php';
require_login();

$db  = db();
$pid = (int)($_GET['product_id'] ?? 0);
$qty = max(1, (int)($_GET['qty'] ?? 1));

if ($pid) {
    $p = $db->prepare("SELECT * FROM products WHERE id = ?");
    $p->execute([$pid]);
    $product = $p->fetch();
}
if (!$product) die('Product not found');

$barcode      = $product['barcode'] ?: $product['sku'];
$company_en   = get_setting('company_name', APP_NAME);
$company_ar   = get_setting('company_name_ar', '');
$currency     = get_setting('currency', 'KWD');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Barcode - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #fff; }
        .no-print { padding: 14px 20px; display: flex; gap: 10px; }
        .labels-wrap { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px; }
        .barcode-card {
            border: 1.5px solid #000;
            padding: 8px 10px;
            text-align: center;
            width: 240px;
            background: #fff;
            page-break-inside: avoid;
        }
        .co-en   { font-size: 13px; font-weight: 900; letter-spacing: 0.5px; color: #000; }
        .co-ar   { font-size: 12px; font-weight: 700; color: #cc0000; direction: rtl; margin-top: 1px; }
        .divider { border: none; border-top: 1px dashed #999; margin: 5px 0; }
        .pr-en   { font-size: 12px; font-weight: 700; color: #000; margin-bottom: 2px; }
        .pr-ar   { font-size: 11px; color: #333; direction: rtl; margin-bottom: 4px; }
        .barcode-img { height: 55px; width: 100%; object-fit: contain; display: block; margin: 4px auto; }
        .sku-txt { font-size: 10px; color: #444; margin-bottom: 3px; font-family: monospace; }
        .price   { font-size: 12px; font-weight: 700; color: #000; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .labels-wrap { gap: 4px; padding: 4px; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 18px;cursor:pointer;font-size:13px">🖨️ Print</button>
        <button onclick="window.close()" style="padding:8px 18px;cursor:pointer;font-size:13px">✕ Close</button>
        <span style="font-size:12px;color:#666;align-self:center">Printing <?= $qty ?> label<?= $qty>1?'s':'' ?></span>
    </div>

    <div class="labels-wrap">
    <?php for ($i = 0; $i < $qty; $i++): ?>
    <div class="barcode-card">
        <div class="co-en"><?= htmlspecialchars($company_en) ?></div>
        <?php if ($company_ar): ?>
        <div class="co-ar"><?= htmlspecialchars($company_ar) ?></div>
        <?php endif; ?>
        <hr class="divider">
        <div class="pr-en"><?= htmlspecialchars($product['name']) ?></div>
        <?php if ($product['name_ar']): ?>
        <div class="pr-ar"><?= htmlspecialchars($product['name_ar']) ?></div>
        <?php endif; ?>
        <img class="barcode-img" src="https://barcodeapi.org/api/128/<?= urlencode($barcode) ?>" alt="<?= htmlspecialchars($barcode) ?>">
        <div class="price">Price: <?= $currency ?> <?= number_format((float)$product['retail_price'], 3) ?></div>
    </div>
    <?php endfor; ?>
    </div>

    <script>window.onload = function(){ window.print(); };</script>
</body>
</html>
