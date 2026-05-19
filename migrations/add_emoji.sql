-- Add emoji column to products table
ALTER TABLE products ADD COLUMN emoji VARCHAR(50) DEFAULT NULL AFTER name_ar;
