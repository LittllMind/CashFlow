# CashFlow

Application PHP vanilla de gestion de caisse, stock, ventes, achats et déclarations (ex-matricothèque).

## Architecture

- `index.php` — routeur frontal.
- `controller/` — logique métier.
- `model/` — accès à la base de données (PDO MySQL).
- `view/` — templates PHP.
- `assets/` — CSS, SCSS, JS.
- `migrations/` — schémas SQL

## Prérequis

- PHP ≥ 8.1
- MySQL/MariaDB
- Composer (optionnel, pour la CI)

## Installation

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/LittllMind/CashFlow.git
   cd CashFlow
   ```

2. Installer les dépendances (optionnel) :
   ```bash
   composer install
   ```

3. Créer les tables avec les migrations dans `migrations/*.sql` (ordre chronologique).

4. Créer `env.php` à la racine et `model/env.php` d'après les modèles `env.php.example` et `model/env_example.php` :
   ```php
   putenv("DATABASE_HOST=127.0.0.1");
   putenv("DATABASE_NAME=cashflow_db");
   putenv("DATABASE_USER=cashflow_user");
   putenv("DATABASE_PASSWORD=votre_mot_de_passe");
   ```

5. Pointer un serveur web (Apache/Nginx) vers la racine du projet.

## sécurité

- `env.php` et `model/env.php` ne sont **jamais** versionnés.
- Les identifiants DB sont injectés via variables d'environnement.
- Les noms d'entités et numéros SIRET dans `model/EntityProfile.php` sont des données de démonstration à remplacer.

## CI

Le workflow GitHub Actions valide :

- la syntaxe `composer.json`
- la syntaxe PHP de tous les fichiers `.php`
- l'absence de `env.php` dans le dépôt

Les tests PHPUnit sont désactivés car ils nécessitent un environnement MySQL. Copier `env.php.example` pour les faire tourner en local.

## Licence

Propriétaire — LittllMind/CashFlow.
