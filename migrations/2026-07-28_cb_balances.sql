-- Migration : table cb_balances pour suivi des virements CB vers Fripe
-- Date: 2026-07-28

CREATE TABLE IF NOT EXISTS cb_balances (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    transfer_date  DATE              NOT NULL COMMENT 'Date du virement effectué',
    cb_amount      DECIMAL(10,2)     NOT NULL COMMENT 'Montant total CB (brut) depuis le dernier virement',
    fee_rate       DECIMAL(5,4)      NOT NULL DEFAULT 0.0146 COMMENT 'Taux de frais CB (ex: 0.0146 = 1.46%)',
    fee_amount     DECIMAL(10,2)     NOT NULL COMMENT 'Montant des frais CB',
    net_amount     DECIMAL(10,2)     NOT NULL COMMENT 'Montant net à virer',
    category       VARCHAR(50)       NOT NULL DEFAULT 'FRIPE' COMMENT 'Pôle concerné (FRIPE, BROC)',
    created_at     DATETIME          NOT NULL DEFAULT NOW(),
    INDEX idx_transfer_date (transfer_date),
    INDEX idx_category_transfer_date (category, transfer_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
