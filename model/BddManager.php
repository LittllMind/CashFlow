<?php

namespace OpenClassroom\Blog\Model;

use PDO;

/**
 * BddManager — Connexion PDO avec config d'environnement.
 *
 * Priorité :
 * 1. Fichier env.php (ou env_prod.php) s'il définit les variables en prod.
 * 2. Variables d'environnement (DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD).
 * 3. Fallback local (localhost/root/cashflow_db).
 *
 * En production, le fichier env.php doit être présent sur le serveur
 * et absent du dépôt git.
 *
 * Si un mode prod doit être auto-détecté, NE JAMAIS encoder de credentials
 * dans le code source. Utiliser l'une des méthodes ci-dessus.
 */
class BddManager
{
    protected function getDbConfig(): array
    {
        $host = getenv('DATABASE_HOST')     ?: 'localhost';
        $database = getenv('DATABASE_NAME') ?: 'cashflow_db';
        $user = getenv('DATABASE_USER')     ?: 'root';
        $password = getenv('DATABASE_PASSWORD') !== false
            ? getenv('DATABASE_PASSWORD')
            : '';

        return [
            'host'     => $host,
            'database' => $database,
            'user'     => $user,
            'password' => $password,
        ];
    }

    protected function dbConnect()
    {
        static $bdd = null;
        if ($bdd === null) {
            $cfg = $this->getDbConfig();
            $bdd = new PDO(
                'mysql:host=' . $cfg['host'] . ';dbname=' . $cfg['database'] . ';charset=utf8',
                $cfg['user'],
                $cfg['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return $bdd;
    }

    protected function dboConnect()
    {
        static $dbo = null;
        if ($dbo === null) {
            $cfg = $this->getDbConfig();
            $dbo = new PDO(
                'mysql:host=' . $cfg['host'] . ';dbname=' . $cfg['database'] . ';charset=utf8',
                $cfg['user'],
                $cfg['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return $dbo;
    }
}
