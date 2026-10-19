-- Migration : journal d'audit des clôtures journalières
-- Objectif : tracer chaque action critique (création, modification, verrouillage, export)
-- pour garantir l'intégrité et la consultabilité en cas de contrôle.

CREATE TABLE IF NOT EXISTS closure_audit_log (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    closure_id INT(11) NOT NULL,
    action ENUM('create','update','lock','export_pdf','export_csv') NOT NULL,
    actor_user_id INT(11) NOT NULL,
    actor_ip VARCHAR(45) NOT NULL,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    previous_hash VARCHAR(64) NULL,
    new_hash VARCHAR(64) NULL,
    metadata JSON NULL,

    KEY idx_closure_id (closure_id),
    KEY idx_action_date (action_date),
    KEY idx_actor (actor_user_id),

    CONSTRAINT fk_audit_closure
        FOREIGN KEY (closure_id) REFERENCES daily_closures(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
