<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class SubcategoryMonthPopularityTest extends TestCase
{
    public function testArticlesManagerHasGetSubcategorySaleCountsByMonth()
    {
        $content = file_get_contents(__DIR__ . '/../../model/ArticlesManager.php');
        $this->assertStringContainsString('function getSubcategorySaleCountsByMonth()', $content);
    }

    public function testArticlesManagerLimitsCountToCurrentMonth()
    {
        $content = file_get_contents(__DIR__ . '/../../model/ArticlesManager.php');
        $this->assertMatchesRegularExpression(
            "/DATE_FORMAT\\(s\\.sale_date, ['\"]%Y-%m['\"]\\)\\s*=\\s*DATE_FORMAT\\(CURDATE\\(\\), ['\"]%Y-%m['\"]\\)/",
            $content,
            'Le comptage doit être limité au mois courant.'
        );
    }

    public function testSalesControllerInjectsSubcategoryScores()
    {
        $content = file_get_contents(__DIR__ . '/../../controller/SalesController.php');
        $this->assertStringContainsString('$subcategoryScores = $articlesManager->getSubcategorySaleCountsByMonth();', $content);
    }

    public function testUpdateTemplateExposesScoresToJs()
    {
        $content = file_get_contents(__DIR__ . '/../../view/control-panel/sale/update/update.php');
        $this->assertTrue(
            str_contains($content, 'json_encode($subcategoriesScores ?? [])') ||
            str_contains($content, 'json_encode($subcategoryScores ?? [])'),
            'update.php doit exposer subcategoryScores au JS.'
        );
    }

    public function testUpdateJsHasHelpers()
    {
        $content = file_get_contents(__DIR__ . '/../../view/control-panel/sale/update/update.js');
        $this->assertStringContainsString('function getSubcategoryScore(category, subcategoryName)', $content);
        $this->assertStringContainsString('function sortByMonthPopularityStable(list, category)', $content);
    }

    public function testUpdateJsSortsByDescendingScoreThenAlphabetical()
    {
        $content = file_get_contents(__DIR__ . '/../../view/control-panel/sale/update/update.js');
        $this->assertStringContainsString('if (sa !== sb) return sb - sa;', $content);
        $this->assertStringContainsString('return a.subcategory_name.localeCompare(b.subcategory_name);', $content);
    }

    public function testUpdateJsAppliesSortToClassicAndStickerCategories()
    {
        $content = file_get_contents(__DIR__ . '/../../view/control-panel/sale/update/update.js');
        $this->assertStringContainsString('catSubcategories = sortByMonthPopularityStable(catSubcategories, category);', $content);
        $matches = preg_match("/sortByMonthPopularityStable\\(\\s*subcategories\\.filter\\(subcat => subcat\\.category_name === 'Sticker'\\),\\s*'Sticker'/s", $content);
        $this->assertSame(1, $matches, 'Le tri doit être appliqué aux stickers.');
    }

    public function testUpdateJsHasNoOldRecentHeading()
    {
        $content = file_get_contents(__DIR__ . '/../../view/control-panel/sale/update/update.js');
        $this->assertStringNotContainsString('🔁 Récents', $content);
    }

    public function testJsSyntaxIsValid()
    {
        $output = [];
        $return = 0;
        exec('node --check ' . escapeshellarg(__DIR__ . '/../../view/control-panel/sale/update/update.js') . ' 2>&1', $output, $return);
        $this->assertSame(0, $return, "Syntaxe JS invalide :\\n" . implode("\\n", $output));
    }

    public function testArticlesManagerPhpSyntaxIsValid()
    {
        $file = __DIR__ . '/../../model/ArticlesManager.php';
        $output = [];
        $return = 0;
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return);
        $this->assertSame(0, $return, "Syntaxe PHP invalide pour $file :\n" . implode("\n", $output));
    }

    public function testSalesControllerPhpSyntaxIsValid()
    {
        $file = __DIR__ . '/../../controller/SalesController.php';
        $output = [];
        $return = 0;
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return);
        $this->assertSame(0, $return, "Syntaxe PHP invalide pour $file :\n" . implode("\n", $output));
    }

    public function testUpdateTemplatePhpSyntaxIsValid()
    {
        $file = __DIR__ . '/../../view/control-panel/sale/update/update.php';
        $output = [];
        $return = 0;
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return);
        $this->assertSame(0, $return, "Syntaxe PHP invalide pour $file :\n" . implode("\n", $output));
    }
}
