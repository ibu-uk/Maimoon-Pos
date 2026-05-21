<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
require_role('super_admin', 'manager');

$tc       = get_tax_config();
$currency = $tc['currency'];
$decimals = $tc['currency_decimals'];
$db       = db();
$user     = current_user();

$_exp_co  = preg_replace('/[^A-Za-z0-9_-]/', '_', trim(get_setting('company_name', 'Company')));
$filename = $_exp_co . '_Refunds_' . date('Y-m-d') . '.csv';

// Branch scope
$is_super   = ($user['role'] === 'super_admin');
$branch_id  = (int)($user['branch_id'] ?? 0);
$bfilter    = $is_super ? "" : "AND i.branch_id = $branch_id";

$refunds = $db->query("
    SELECT
        p.created_at,
        COALESCE(c.name, 'Walk-in') as customer_name,
        i.invoice_number,
        b.name as branch_name,
        p.amount,
        p.payment_mode,
        REPLACE(SUBSTRING_INDEX(p.notes, ' - ', -1), 'Refund: ', '') as reason,
        u.name as processed_by
    FROM payments p
    LEFT JOIN customers c  ON c.id = p.reference_id
    LEFT JOIN invoices i   ON i.id = p.invoice_id
    LEFT JOIN branches b   ON b.id = i.branch_id
    LEFT JOIN users u      ON u.id = p.created_by
    WHERE p.type = 'customer'
    AND p.notes LIKE 'Refund:%'
    $bfilter
    ORDER BY p.created_at DESC
")->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

fputcsv($out, ['Date', 'Customer', 'Invoice #', 'Branch', 'Amount (' . $currency . ')', 'Method', 'Reason', 'Processed By']);

foreach ($refunds as $r) {
    fputcsv($out, [
        date('Y-m-d H:i', strtotime($r['created_at'])),
        $r['customer_name'],
        $r['invoice_number'] ?? '',
        $r['branch_name'] ?? '',
        number_format($r['amount'], $decimals, '.', ''),
        strtoupper($r['payment_mode']),
        $r['reason'],
        $r['processed_by'] ?? '',
    ]);
}

fclose($out);
exit;
