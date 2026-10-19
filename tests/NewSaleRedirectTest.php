<?php
/**
 * Test RED/GREEN — Après création d'une vente, l'URL doit contenir sale_id.
 *
 * Reproduit le bug : en cliquant sur "Nouvelle vente", le formulaire d'ajout
 * s'affiche sans sale_id dans l'URL, donc on ne peut pas modifier cette vente
 * immédiatement (rechargement = nouvelle vente, ajout d'article sans cible).
 */

require_once __DIR__ . '/../model/BddManager.php';

class TestDbConn extends \OpenClassroom\Blog\Model\BddManager
{
    public function connect()
    {
        return $this->dbConnect();
    }
}

function assertTrue($condition, $msg)
{
    static $passed = 0, $failed = 0;
    if ($condition) {
        echo '  PASS : ' . $msg . PHP_EOL;
        $passed++;
    } else {
        echo '  FAIL : ' . $msg . PHP_EOL;
        $failed++;
    }
    return [$passed, $failed];
}

function bail($msg)
{
    echo '  ABORT : ' . $msg . PHP_EOL;
    exit(1);
}

$baseUrl = getenv('MATRICO_TEST_URL') ?: 'http://127.0.0.1:8000';
$pseudo  = 'hermes-test-nsr';
$email   = 'hermes-test-nsr@example.com';
$pass    = getenv('MATRICO_TEST_PASS') ?: bin2hex(random_bytes(8));

// 1. Créer / mettre à jour un utilisateur admin de test (local uniquement)
try {
    $pdo = (new TestDbConn())->connect();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE pseudo = ?');
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    if ($user) {
        $pdo->prepare('UPDATE users SET password = ?, user_status = ?, fonction = ? WHERE id = ?')
            ->execute([$hash, 'admin', 'admin', $user['id']]);
    } else {
        $pdo->prepare('INSERT INTO users (pseudo, password, mail, user_status, fonction) VALUES (?, ?, ?, ?, ?)')
            ->execute([$pseudo, $hash, $email, 'admin', 'admin']);
    }
} catch (Throwable $e) {
    bail('Connexion DB locale impossible : ' . $e->getMessage());
}

// 2. Login via curl avec cookie jar
$jar = tempnam(sys_get_temp_dir(), 'matric_jar');
$loginUrl = $baseUrl . '/index.php?action=memberConnexion';

$ch = curl_init($loginUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR      => $jar,
    CURLOPT_COOKIEFILE     => $jar,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['pseudo' => $pseudo, 'password' => $pass]),
]);
$loginBody = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$loginUrl  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

if ($loginCode !== 200 || strpos($loginUrl, 'salePanel') === false) {
    bail("Login a échoué (HTTP $loginCode, final $loginUrl) — le serveur local est-il démarré ?");
}

// 3. Appeler newSaleForm et suivre les redirections
$createUrl = $baseUrl . '/index.php?action=newSaleForm';
$ch = curl_init($createUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR      => $jar,
    CURLOPT_COOKIEFILE     => $jar,
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);
unlink($jar);

if ($code !== 200) {
    bail("newSaleForm a retourné HTTP $code");
}

echo 'Final URL : ' . $finalUrl . PHP_EOL;

// 4. Assertions
assertTrue(strpos($finalUrl, 'sale_id=') !== false, 'L\'URL finale contient sale_id après création');

preg_match('/sale_id=(\d+)/', $finalUrl, $matches);
$saleIdFromUrl = $matches[1] ?? null;
assertTrue($saleIdFromUrl !== null && ctype_digit($saleIdFromUrl), 'sale_id dans l\'URL est un entier numérique');

preg_match('/<input[^>]*id=["\']sale_id["\'][^>]*value=["\']([^"\']+)["\'][^>]*>/i', $body, $inputMatches);
$saleIdInput = $inputMatches[1] ?? null;
assertTrue($saleIdInput !== null && ctype_digit($saleIdInput), 'L\'input hidden #sale_id contient un ID numérique');

assertTrue($saleIdFromUrl === $saleIdInput, 'sale_id URL == sale_id input #sale_id');

assertTrue(strpos($body, "value='add'") === false && strpos($body, 'value="add"') === false,
    'Le formulaire n\'est plus en mode "add" générique');

// 5. Bilan
$results = assertTrue(true, ''); // récupère les compteurs
list($passed, $failed) = $results;
echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
