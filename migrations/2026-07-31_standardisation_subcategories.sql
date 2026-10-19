-- ============================================
-- Migration: Standardisation subcategories activity
-- Date: 2026-07-31
-- Auteur: Hermes
-- ============================================

-- 1. Uniformiser Accessoires en FRIPE (était mixte FRIPE/BROC)
UPDATE subcategories SET activity = 'FRIPE' WHERE category_name = 'Accessoires';

-- 2. Mercerie et Bougies en FRIPE (alignement règle métier)
UPDATE subcategories SET activity = 'FRIPE' WHERE category_name = 'Mercerie';
UPDATE subcategories SET activity = 'FRIPE' WHERE category_name = 'Bougies';

-- 3. Créer subcategories manquantes (articles orphelins)
INSERT INTO subcategories (category_id, category_name, subcategory_name, activity) VALUES
( (SELECT id FROM categories WHERE category_name = 'Femmes' LIMIT 1), 'Femmes', 'Jupes', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Femmes' LIMIT 1), 'Femmes', 'Robes', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Femmes' LIMIT 1), 'Femmes', 'Tops et chemisiers', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Hommes' LIMIT 1), 'Hommes', 'Combi/salopettes', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Hommes' LIMIT 1), 'Hommes', 'T-shirts', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Brocante' LIMIT 1), 'Brocante', 'Tampons', 'BROC'),
( (SELECT id FROM categories WHERE category_name = 'Brocante' LIMIT 1), 'Brocante', 'Couteaux', 'BROC'),
( (SELECT id FROM categories WHERE category_name = 'Accessoires' LIMIT 1), 'Accessoires', 'Mouchoirs', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Accessoires' LIMIT 1), 'Accessoires', 'Bijoux', 'FRIPE'),
( (SELECT id FROM categories WHERE category_name = 'Collab' LIMIT 1), 'Collab', 'vynile', 'BROC'),
( (SELECT id FROM categories WHERE category_name = 'Enfants' LIMIT 1), 'Enfants', 'Jouet', 'FRIPE') -- corr orphelin
ON DUPLICATE KEY UPDATE activity = VALUES(activity);

-- 4. Standardiser typos/articles vers subcategories existantes
UPDATE articles SET subcategory = 'Jupe' WHERE subcategory = 'Jupes';
UPDATE articles SET subcategory = 'Robe' WHERE subcategory = 'Robes';
UPDATE articles SET subcategory = 'Combi/salopette' WHERE subcategory = 'Combi/salopettes';
UPDATE articles SET subcategory = 'T-shirt' WHERE subcategory = 'T-shirts';
UPDATE articles SET subcategory = 'Top' WHERE subcategory = 'Tops et chemisiers';
UPDATE articles SET subcategory = 'Vinyle' WHERE subcategory = 'vynile';
UPDATE articles SET subcategory = 'Mouchoir' WHERE subcategory = 'Mouchoirs';
UPDATE articles SET subcategory = 'Autres' WHERE subcategory = 'Bijoux';
UPDATE articles SET category = 'Mercerie' WHERE category LIKE 'Mercerie%' AND category != 'Mercerie';
UPDATE articles SET category = 'Livres', subcategory = 'Ancien' WHERE category = 'Livre';

-- 5. Supprimer subcategories sans articles (< 10 articles identifiées audit)
-- NOTE: Ne pas supprimer celles avec encore des articles (sécurité)
-- Les suivantes ont été identifiées comme vides après standardisation:
DELETE FROM subcategories WHERE subcategory_name IN (
    'Argile','Cobra (9€)','Creme Barbarie','Cylindre abeille (9€)',
    'Cylindre Renard (13€)','Étiquettes','Huile Barbarie','Journaux',
    'Loup (9€)','Moto (9€)','Ours (9€)','Sorcier (9€)','Tricot'
);
