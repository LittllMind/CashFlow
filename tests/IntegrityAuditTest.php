#<?php
/**
 * IntegrityAuditTest.php — Verifie la coherence sales.total_amount vs somme articles
 *
 * Usage : php tests/IntegrityAuditTest.php [tolerance]
 * Sortie : 0 = OK, 1 = ecarts detectes (affiche details)
 */
require_once __DIR__ . '/../env.php';

class IntegrityAuditTest
{
    private $bdd;
    private $tolerance = 0.01;

    public function __construct(float $tolerance = 0.01)
    {
        $this->tolerance = $tolerance;
        $host = getenv('DATABASE_HOST')     ?: 'localhost';
        $db   = getenv('DATABASE_NAME')     ?: 'matricotheque_local';
        $user = getenv('DATABASE_USER')     ?: 'root';
        $pass = getenv('DATABASE_PASSWORD') !== false ? getenv('DATABASE_PASSWORD') : '';
        $this->bdd = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function run(): int
    {
        $req = $this->bdd->prepare("
            SELECT s.id, s.total_amount,
                   COALESCE(SUM(a.quantity * a.unit_price), 0) AS calc_total
            FROM sales s
            LEFT JOIN articles a ON s.id = a.sale_id AND a.sale_id IS NOT NULL
            WHERE s.client_id IS NULL
            GROUP BY s.id
            HAVING ABS(s.total_amount - COALESCE(SUM(a.quantity * a.unit_price), 0)) > :tolerance
            ORDER BY ABS(s.total_amount - COALESCE(SUM(a.quantity * a.unit_price), 0)) DESC
        ");
        $req->execute(['tolerance' => $this->tolerance]);
        $ecarts = $req->fetchAll(PDO::FETCH_ASSOC);

        if (count($ecarts) === 0) {
            echo "PASS — Aucun ecart detecte (tolerance " . $this->tolerance . ")\n";
            return 0;
        }

        echo "FAIL — " . count($ecarts) . " vente(s) avec ecart :\n";
        echo str_repeat("-", 60) . "\n";
        printf("%-8s %-12s %-12s %-10s\n", "sale_id", "total_amount", "calc_total", "ecart");
        foreach ($ecarts as $e) {
            $ecart = round($e['total_amount'] - $e['calc_total'], 2);
            printf("%-8d %-12.2f %-12.2f %-+10.2f\n", $e['id'], $e['total_amount'], $e['calc_total'], $ecart);
        }
        echo str_repeat("-", 60) . "\n";
        return 1;
    }
}

// CLI entry point
if (php_sapi_name() === 'cli') {
    $tolerance = isset($argv[1]) ? floatval($argv[1]) : 0.01;
    $test = new IntegrityAuditTest($tolerance);
    exit($test->run());
}
