<?php
namespace phpbu\App\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * Generator test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class GeneratorTest extends TestCase
{
    /**
     * Tests Generator::generateConfigurationSkeleton
     */
    public function testGenerateConfigurationSkeletonXml()
    {
        $generator = new Generator();
        $config    = $generator->generateConfigurationSkeleton('X.Y', 'xml', 'boot.php');

        $this->assertStringContainsString('bootstrap="boot.php"', $config);
        $this->assertStringContainsString('xsi:noNamespaceSchemaLocation="https://schema.phpbu.de/X.Y/phpbu.xsd"', $config);
    }

    /**
     * Tests Generator::generateConfigurationSkeleton
     */
    public function testGenerateConfigurationSkeletonJson()
    {
        $generator = new Generator();
        $config    = $generator->generateConfigurationSkeleton('X.Y', 'json', 'boot.php');

        $this->assertStringContainsString('"bootstrap": "boot.php"', $config);
    }
}
