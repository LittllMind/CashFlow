-- migrations/2026-07-30_urssaf_declarations.sql
-- Objectif : suivi des declarations URSSAF par activite et periode
-- Rollback : DROP TABLE IF EXISTS urssaf_declarations;

CREATE TABLE IF NOT EXISTS urssaf_declarations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity VARCHAR(20) NOT NULL COMMENT 'broc ou fripe',
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_ca DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_cash DECIMAL(12,2) DEFAULT 0,
    total_card DECIMAL(12,2) DEFAULT 0,
    total_cheque DECIMAL(12,2) DEFAULT 0,
    total_bank_transfer DECIMAL(12,2) DEFAULT 0,
    nb_sales INT DEFAULT 0,
    nb_articles INT DEFAULT 0,
    declared_at DATETIME DEFAULT NULL,
    declared_by VARCHAR(100) DEFAULT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_urssaf_period (activity, period_start, period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
