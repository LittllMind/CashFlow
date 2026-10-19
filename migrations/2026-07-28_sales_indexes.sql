-- Migration : index performance sur sales (sale_date, payment_method)
-- Date: 2026-07-28

ALTER TABLE sales ADD INDEX idx_sale_date (sale_date);
ALTER TABLE sales ADD INDEX idx_payment_method (payment_method);
ALTER TABLE sales ADD INDEX idx_sale_date_payment (sale_date, payment_method);
