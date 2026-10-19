<?php
// tests/SubcategoryMonthPopularityTest.php — wrapper autour de la suite PHPUnit

$output = [];
$return = 0;
exec('php vendor/bin/phpunit --no-coverage tests/Phpunit/SubcategoryMonthPopularityTest.php 2>&1', $output, $return);
foreach ($output as $line) {
    echo $line . PHP_EOL;
}
exit($return);
