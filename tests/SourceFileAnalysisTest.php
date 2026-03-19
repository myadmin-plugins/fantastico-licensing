<?php

namespace Detain\MyAdminFantastico\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Static analysis tests for source files.
 *
 * Uses file_get_contents to inspect source files for expected structures,
 * function definitions, and coding patterns without requiring database
 * or external service dependencies.
 */
class SourceFileAnalysisTest extends TestCase
{
    /**
     * @var string Base path to the src directory.
     */
    private static $srcDir;

    public static function setUpBeforeClass(): void
    {
        self::$srcDir = dirname(__DIR__) . '/src';
    }

    // ---------------------------------------------------------------
    // File existence tests
    // ---------------------------------------------------------------

    /**
     * Tests that the Plugin.php source file exists.
     */
    public function testPluginFileExists(): void
    {
        $this->assertFileExists(self::$srcDir . '/Plugin.php');
    }

    /**
     * Tests that the fantastico.inc.php source file exists.
     */
    public function testFantasticoIncFileExists(): void
    {
        $this->assertFileExists(self::$srcDir . '/fantastico.inc.php');
    }

    /**
     * Tests that the fantastico_licenses_list.php source file exists.
     */
    public function testFantasticoLicensesListFileExists(): void
    {
        $this->assertFileExists(self::$srcDir . '/fantastico_licenses_list.php');
    }

    /**
     * Tests that the fantastico_list.php source file exists.
     */
    public function testFantasticoListFileExists(): void
    {
        $this->assertFileExists(self::$srcDir . '/fantastico_list.php');
    }

    /**
     * Tests that the reusable_fantastico.php source file exists.
     */
    public function testReusableFantasticoFileExists(): void
    {
        $this->assertFileExists(self::$srcDir . '/reusable_fantastico.php');
    }

    // ---------------------------------------------------------------
    // Plugin.php static analysis
    // ---------------------------------------------------------------

    /**
     * Tests that Plugin.php declares the correct namespace.
     */
    public function testPluginFileDeclaresNamespace(): void
    {
        $content = file_get_contents(self::$srcDir . '/Plugin.php');
        $this->assertStringContainsString('namespace Detain\\MyAdminFantastico;', $content);
    }

    /**
     * Tests that Plugin.php uses the Fantastico class.
     */
    public function testPluginFileUsesFantasticoClass(): void
    {
        $content = file_get_contents(self::$srcDir . '/Plugin.php');
        $this->assertStringContainsString('use Detain\\Fantastico\\Fantastico;', $content);
    }

    /**
     * Tests that Plugin.php uses GenericEvent.
     */
    public function testPluginFileUsesGenericEvent(): void
    {
        $content = file_get_contents(self::$srcDir . '/Plugin.php');
        $this->assertStringContainsString('use Symfony\\Component\\EventDispatcher\\GenericEvent;', $content);
    }

    /**
     * Tests that Plugin.php declares the class.
     */
    public function testPluginFileDeclaresClass(): void
    {
        $content = file_get_contents(self::$srcDir . '/Plugin.php');
        $this->assertMatchesRegularExpression('/class\s+Plugin/', $content);
    }

    // ---------------------------------------------------------------
    // fantastico.inc.php static analysis
    // ---------------------------------------------------------------

    /**
     * Tests that fantastico.inc.php defines the get_fantastico_licenses function.
     */
    public function testIncFileDefinesGetFantasticoLicenses(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression('/function\s+get_fantastico_licenses\s*\(/', $content);
    }

    /**
     * Tests that fantastico.inc.php defines the get_fantastico_list function.
     */
    public function testIncFileDefinesGetFantasticoList(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression('/function\s+get_fantastico_list\s*\(/', $content);
    }

    /**
     * Tests that fantastico.inc.php defines the get_available_fantastico function.
     */
    public function testIncFileDefinesGetAvailableFantastico(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression('/function\s+get_available_fantastico\s*\(/', $content);
    }

    /**
     * Tests that fantastico.inc.php defines the activate_fantastico function.
     */
    public function testIncFileDefinesActivateFantastico(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression('/function\s+activate_fantastico\s*\(/', $content);
    }

    /**
     * Tests that fantastico.inc.php defines the get_reusable_fantastico function.
     */
    public function testIncFileDefinesGetReusableFantastico(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression('/function\s+get_reusable_fantastico\s*\(/', $content);
    }

    /**
     * Tests that fantastico.inc.php contains exactly 5 function definitions.
     */
    public function testIncFileHasExpectedFunctionCount(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        preg_match_all('/^\s*function\s+\w+\s*\(/m', $content, $matches);
        $this->assertCount(5, $matches[0], 'fantastico.inc.php should define exactly 5 functions');
    }

    /**
     * Tests that activate_fantastico function accepts two parameters.
     */
    public function testActivateFantasticoAcceptsTwoParams(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression(
            '/function\s+activate_fantastico\s*\(\s*\$\w+\s*,\s*\$\w+\s*\)/',
            $content
        );
    }

    /**
     * Tests that get_available_fantastico function accepts one parameter.
     */
    public function testGetAvailableFantasticoAcceptsOneParam(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression(
            '/function\s+get_available_fantastico\s*\(\s*\$\w+\s*\)/',
            $content
        );
    }

    /**
     * Tests that get_fantastico_licenses takes no parameters.
     */
    public function testGetFantasticoLicensesNoParams(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression(
            '/function\s+get_fantastico_licenses\s*\(\s*\)/',
            $content
        );
    }

    /**
     * Tests that get_reusable_fantastico takes no parameters.
     */
    public function testGetReusableFantasticoNoParams(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertMatchesRegularExpression(
            '/function\s+get_reusable_fantastico\s*\(\s*\)/',
            $content
        );
    }

    /**
     * Tests that fantastico.inc.php uses the Fantastico class.
     */
    public function testIncFileUsesFantasticoClass(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString('use Detain\\Fantastico\\Fantastico;', $content);
    }

    /**
     * Tests that fantastico.inc.php references Fantastico::ALL_TYPES constant.
     */
    public function testIncFileReferencesAllTypesConstant(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString('Fantastico::ALL_TYPES', $content);
    }

    /**
     * Tests that activate_fantastico uses ini_set for max_execution_time.
     */
    public function testActivateFantasticoSetsMaxExecTime(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString("ini_set('max_execution_time'", $content);
    }

    /**
     * Tests that activate_fantastico uses ini_set for default_socket_timeout.
     */
    public function testActivateFantasticoSetsSocketTimeout(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString("ini_set('default_socket_timeout'", $content);
    }

    /**
     * Tests that activate_fantastico returns boolean or result id.
     */
    public function testActivateFantasticoHasReturnStatements(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString('return true;', $content);
        $this->assertStringContainsString('return false;', $content);
    }

    /**
     * Tests that get_fantastico_list references both licenses and vps modules.
     */
    public function testGetFantasticoListChecksModules(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString("\$GLOBALS['modules']['licenses']", $content);
        $this->assertStringContainsString("\$GLOBALS['modules']['vps']", $content);
    }

    /**
     * Tests that fantastico.inc.php has proper docblock header.
     */
    public function testIncFileHasDocblockHeader(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico.inc.php');
        $this->assertStringContainsString('@author', $content);
        $this->assertStringContainsString('@package', $content);
        $this->assertStringContainsString('@category', $content);
    }

    // ---------------------------------------------------------------
    // fantastico_licenses_list.php static analysis
    // ---------------------------------------------------------------

    /**
     * Tests that fantastico_licenses_list.php defines the expected function.
     */
    public function testLicensesListFileDefinesFunction(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_licenses_list.php');
        $this->assertMatchesRegularExpression('/function\s+fantastico_licenses_list\s*\(/', $content);
    }

    /**
     * Tests that fantastico_licenses_list function checks admin access.
     */
    public function testLicensesListChecksAdmin(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_licenses_list.php');
        $this->assertStringContainsString("\$GLOBALS['tf']->ima == 'admin'", $content);
    }

    /**
     * Tests that fantastico_licenses_list uses TFTable.
     */
    public function testLicensesListUsesTFTable(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_licenses_list.php');
        $this->assertStringContainsString('new \\TFTable()', $content);
    }

    /**
     * Tests that fantastico_licenses_list.php contains exactly 1 function.
     */
    public function testLicensesListHasOneFunctionDefinition(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_licenses_list.php');
        preg_match_all('/^\s*function\s+\w+\s*\(/m', $content, $matches);
        $this->assertCount(1, $matches[0]);
    }

    // ---------------------------------------------------------------
    // fantastico_list.php static analysis
    // ---------------------------------------------------------------

    /**
     * Tests that fantastico_list.php defines the expected function.
     */
    public function testListFileDefinesFunction(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_list.php');
        $this->assertMatchesRegularExpression('/function\s+fantastico_list\s*\(/', $content);
    }

    /**
     * Tests that fantastico_list function checks admin access.
     */
    public function testListChecksAdmin(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_list.php');
        $this->assertStringContainsString("\$GLOBALS['tf']->ima == 'admin'", $content);
    }

    /**
     * Tests that fantastico_list calls page_title.
     */
    public function testListCallsPageTitle(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_list.php');
        $this->assertStringContainsString("page_title('Fantastico License List')", $content);
    }

    /**
     * Tests that fantastico_list calls render_form.
     */
    public function testListCallsRenderForm(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_list.php');
        $this->assertStringContainsString("render_form('fantastico_list')", $content);
    }

    /**
     * Tests that fantastico_list.php contains exactly 1 function.
     */
    public function testListFileHasOneFunctionDefinition(): void
    {
        $content = file_get_contents(self::$srcDir . '/fantastico_list.php');
        preg_match_all('/^\s*function\s+\w+\s*\(/m', $content, $matches);
        $this->assertCount(1, $matches[0]);
    }

    // ---------------------------------------------------------------
    // reusable_fantastico.php static analysis
    // ---------------------------------------------------------------

    /**
     * Tests that reusable_fantastico.php defines the expected function.
     */
    public function testReusableFileDefinesFunction(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertMatchesRegularExpression('/function\s+reusable_fantastico\s*\(/', $content);
    }

    /**
     * Tests that reusable_fantastico function checks admin access.
     */
    public function testReusableChecksAdmin(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertStringContainsString("\$GLOBALS['tf']->ima == 'admin'", $content);
    }

    /**
     * Tests that reusable_fantastico uses TFTable.
     */
    public function testReusableUsesTFTable(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertStringContainsString('new \\TFTable()', $content);
    }

    /**
     * Tests that reusable_fantastico handles the add action.
     */
    public function testReusableHandlesAddAction(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertStringContainsString("['add']", $content);
    }

    /**
     * Tests that reusable_fantastico references service type IDs.
     */
    public function testReusableReferencesServiceTypeIds(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertStringContainsString('5013', $content);
        $this->assertStringContainsString('5003', $content);
    }

    /**
     * Tests that reusable_fantastico.php has docblock with throws annotations.
     */
    public function testReusableHasThrowsAnnotations(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertStringContainsString('@throws \\Exception', $content);
        $this->assertStringContainsString('@throws \\SmartyException', $content);
    }

    /**
     * Tests that reusable_fantastico calls render_form.
     */
    public function testReusableCallsRenderForm(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        $this->assertStringContainsString("render_form('reusable_fantastico')", $content);
    }

    /**
     * Tests that reusable_fantastico.php contains exactly 1 function.
     */
    public function testReusableHasOneFunctionDefinition(): void
    {
        $content = file_get_contents(self::$srcDir . '/reusable_fantastico.php');
        preg_match_all('/^\s*function\s+\w+\s*\(/m', $content, $matches);
        $this->assertCount(1, $matches[0]);
    }

    // ---------------------------------------------------------------
    // Cross-file consistency
    // ---------------------------------------------------------------

    /**
     * Tests that all PHP source files open with a PHP tag.
     */
    public function testAllSourceFilesStartWithPhpTag(): void
    {
        $files = glob(self::$srcDir . '/*.php');
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringStartsWith('<?php', $content, basename($file) . ' should start with <?php');
        }
    }

    /**
     * Tests that all source files using Fantastico import the class.
     */
    public function testAllFilesUsingFantasticoImportIt(): void
    {
        $files = glob(self::$srcDir . '/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'new Fantastico(') !== false || strpos($content, 'Fantastico::') !== false) {
                $this->assertStringContainsString(
                    'use Detain\\Fantastico\\Fantastico;',
                    $content,
                    basename($file) . ' uses Fantastico but does not import it'
                );
            }
        }
    }

    /**
     * Tests that Plugin.php references the correct source files in getRequirements.
     */
    public function testPluginRequirementsReferenceExistingFiles(): void
    {
        $content = file_get_contents(self::$srcDir . '/Plugin.php');
        $referencedFiles = [
            'fantastico.inc.php',
            'fantastico_licenses_list.php',
            'fantastico_list.php',
            'reusable_fantastico.php',
        ];
        foreach ($referencedFiles as $file) {
            $this->assertStringContainsString(
                $file,
                $content,
                "Plugin.php should reference {$file} in getRequirements"
            );
        }
    }

    /**
     * Tests that all source files have proper docblock headers.
     */
    public function testAllSourceFilesHaveDocblocks(): void
    {
        $files = glob(self::$srcDir . '/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringContainsString(
                '/**',
                $content,
                basename($file) . ' should contain at least one docblock'
            );
        }
    }

    /**
     * Tests the total count of source files in src directory.
     */
    public function testSourceFileCount(): void
    {
        $files = glob(self::$srcDir . '/*.php');
        $this->assertCount(5, $files, 'There should be exactly 5 PHP source files');
    }
}
