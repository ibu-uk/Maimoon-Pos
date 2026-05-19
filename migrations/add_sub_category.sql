-- Add sub_category_id column to products table
ALTER TABLE products ADD COLUMN sub_category_id INT DEFAULT NULL AFTER category_id;
ALTER TABLE products ADD FOREIGN KEY (sub_category_id) REFERENCES categories(id) ON DELETE SET NULL;
