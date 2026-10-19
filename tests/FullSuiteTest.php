<?php
// tests/FullSuiteTest.php — Lance toutes les suites

$suites = [
    'AuthCleanTest.php',
    'DateNavTest.php',
    'NewSaleRedirectTest.php',
    'PostSaleArticleIdTest.php',
    'UxPolishTest.php',
    'ArdoisePanelTest.php',
    'SalePanelLayoutTest.php',
    'SalePanelModalTest.php',
    'BougiesSubcategoriesTest.php',
    'SubcategoryMonthPopularityTest.php',
    'StockPanelTest.php',
    'SaleDateArticleSyncTest.php',
    'PurchasePanelTest.php',
    'PurchaseFormTest.php',
    'DailyClosureTest.php',
    'TicketExportTest.php',
    'VerifyNoDoubleCount.php',
    'VerifyDeleteUpdateNoDoubleCount.php',
    'UrssafReportTest.php',
    'ArticlesYearTabsTest.php',
];

$totalPassed = 0;
$totalFailed = 0;

foreach ($suites as $suite) {
    echo '=== ' . $suite . ' ===' . PHP_EOL;
    $output = [];
    $return = 0;
    exec('php ' . __DIR__ . '/' . $suite, $output, $return);
    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }
    if ($return === 0) {
        $totalPassed++;
    } else {
        $totalFailed++;
    }
}

echo PHP_EOL . 'Suites : ' . $totalPassed . ' OK, ' . $totalFailed . ' KO' . PHP_EOL;
exit($totalFailed > 0 ? 1 : 0);
