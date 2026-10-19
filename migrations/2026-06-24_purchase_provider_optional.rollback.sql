-- Rollback : revenir à provider_id NOT NULL (nécessite qu'aucune ligne ne soit NULL)
ALTER TABLE purchases MODIFY provider_id INT NOT NULL;
