-- Migration : permettre un provider_id NULL pour les achats sans fournisseur (particulier, don, vide-grenier)
-- Auteur : Hermes
-- Date : 2026-06-24
-- DB cible : cashflow_db

ALTER TABLE purchases MODIFY provider_id INT NULL;
