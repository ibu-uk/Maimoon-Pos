-- Add origin_country column to products table
ALTER TABLE products ADD COLUMN origin_country VARCHAR(100) DEFAULT NULL AFTER brand;
