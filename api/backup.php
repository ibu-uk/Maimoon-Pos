<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
require_role('super_admin');

$db       = db();
$dbname   = DB_NAME;
$co_name  = preg_replace('/[^A-Za-z0-9_-]/', '_', trim(get_setting('company_name', 'retailpro')));
$filename = $co_name . '_backup_' . date('Ymd_His') . '.sql';

// Stream as downloadable file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$nl = "\n";

// ── Header ────────────────────────────────────────────────────────────────────
echo "-- RetailPro Database Backup" . $nl;
echo "-- Database : $dbname" . $nl;
echo "-- Company  : " . get_setting('company_name', 'RetailPro') . $nl;
echo "-- Generated: " . date('Y-m-d H:i:s') . $nl;
echo "-- ---------------------------------------------------------" . $nl . $nl;
echo "SET FOREIGN_KEY_CHECKS=0;" . $nl;
echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';" . $nl;
echo "SET NAMES utf8mb4;" . $nl . $nl;

// ── Get all tables ─────────────────────────────────────────────────────────────
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {

    // ── DROP + CREATE TABLE ───────────────────────────────────────────────────
    echo "-- --------------------------------------------------------" . $nl;
    echo "-- Table: `$table`" . $nl;
    echo "-- --------------------------------------------------------" . $nl . $nl;
    echo "DROP TABLE IF EXISTS `$table`;" . $nl;

    $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
    echo $create[1] . ";" . $nl . $nl;

    // ── INSERT rows ───────────────────────────────────────────────────────────
    $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo $nl;
        continue;
    }

    // Column names
    $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';

    // Chunk into batches of 100 rows for readable output
    $chunks = array_chunk($rows, 100);
    foreach ($chunks as $chunk) {
        echo "INSERT INTO `$table` ($cols) VALUES" . $nl;
        $lines = [];
        foreach ($chunk as $row) {
            $vals = array_map(function($v) use ($db) {
                if ($v === null) return 'NULL';
                if (is_numeric($v) && !preg_match('/^0\d/', $v)) return $v;
                return $db->quote($v);
            }, array_values($row));
            $lines[] = '  (' . implode(', ', $vals) . ')';
        }
        echo implode(',' . $nl, $lines) . ';' . $nl;
    }
    echo $nl;
}

echo "SET FOREIGN_KEY_CHECKS=1;" . $nl;
echo $nl . "-- Backup complete." . $nl;
exit;
