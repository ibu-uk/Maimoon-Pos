-- RetailPro: Tax & Currency configuration migration
-- Run this once on your database before deploying the new settings.php

-- Insert default settings (Kuwait profile — no tax)
-- ON DUPLICATE KEY UPDATE means it won't overwrite values you've already set

INSERT INTO settings (setting_key, setting_value) VALUES
  ('country_code',       'KW'),
  ('currency_decimals',  '3'),
  ('tax_type',           'none'),
  ('tax_rate',           '0'),
  ('tax_label',          ''),
  ('tax_inclusive',      '0')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Keep existing currency/vat_number rows untouched
-- (currency was already in settings, vat_number was already in settings)

