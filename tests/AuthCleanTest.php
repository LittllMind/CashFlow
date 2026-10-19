<?php
// tests/AuthCleanTest.php

$passed = 0;
$failed = 0;

function assertTrue($condition, $msg) {
    global $passed, $failed;
    if ($condition) {
        echo '  PASS : ' . $msg . PHP_EOL;
        $passed++;
    } else {
        echo '  FAIL : ' . $msg . PHP_EOL;
        $failed++;
    }
}

$mainController = file_get_contents(__DIR__ . '/../controller/MainController.php');
assertTrue(
    strpos($mainController, 'function userValidation($pseudo, $password)') === false,
    'userValidation() sans args : suppression signature legacy'
);
assertTrue(
    strpos($mainController, 'function userValidation()') !== false,
    'userValidation() sans args : nouvelle signature presente'
);

$index = file_get_contents(__DIR__ . '/../index.php');
assertTrue(
    strpos($index, '\$_SESSION[\'password\']') === false,
    'index.php : plus de session password'
);
assertTrue(
    strpos($index, 'Migration session legacy') === false,
    'index.php : plus de bloc migration legacy'
);

$connexion = file_get_contents(__DIR__ . '/../view/users/connexion.php');
assertTrue(
    strpos($connexion, '\$_SESSION[\'password\']') === false,
    'connexion.php : plus de session password'
);

$dirs = [__DIR__ . '/../controller'];
$badCalls = 0;
foreach ($dirs as $dir) {
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $badCalls += substr_count($content, 'userValidation(\$_SESSION[\'pseudo\'], \$_SESSION[\'password\'])');
    }
}
$content = file_get_contents(__DIR__ . '/../index.php');
$badCalls += substr_count($content, 'userValidation(\$_SESSION[\'pseudo\'], \$_SESSION[\'password\'])');
assertTrue(
    $badCalls === 0,
    'index.php/controller/*.php : plus appels userValidation(session pseudo/password)'
);

assertTrue(
    strpos($mainController, '\$_SESSION[\'password\']') === false,
    'MainController.php : plus de session password'
);
assertTrue(
    strpos($mainController, 'userConnection(\$_SESSION[\'mail\'], \$_SESSION[\'password\']') === false,
    'login() : plus de passage du password legacy'
);

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
