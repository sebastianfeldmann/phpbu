<?php
namespace phpbu\App\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * Finder test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class FinderTest extends TestCase
{
    /**
     * Tests Finder::findConfiguration
     */
    public function testFindConfigurationNoConfigInDir()
    {
        $this->expectException('phpbu\App\Exception');
        $finder = new Finder();
        $finder->findConfiguration(PHPBU_TEST_FILES . '/conf');
    }

    /**
     * Tests Finder::findConfiguration
     */
    public function testFindConfigurationInvalidPath()
    {
        $this->expectException('phpbu\App\Exception');
        $finder = new Finder();
        $finder->findConfiguration(PHPBU_TEST_FILES . '/fooBarBaz');
    }

    /**
     * Tests Finder::findConfiguration
     */
    public function testFindDefaultConfFail()
    {
        $this->expectException('phpbu\App\Exception');
        $old = getcwd();
        chdir(PHPBU_TEST_FILES . '/conf');

        try {
            $finder = new Finder();
            $finder->findConfiguration('');
        } catch (\Exception $e) {
            chdir($old);
            throw $e;
        }
    }

    /**
     * Tests Finder::findConfiguration
     */
    public function testFindDefaultConfSuccess()
    {
        $expected = PHPBU_TEST_FILES . '/conf/default-xml/phpbu.xml';
        $old      = getcwd();
        chdir(PHPBU_TEST_FILES . '/conf/default-xml');

        try {
            $finder = new Finder();
            $config = $finder->findConfiguration('');
        } catch (\Exception $e) {
            chdir($old);
            throw $e;
        }

        chdir($old);
        $this->assertEquals($expected, $config);
    }

    /**
     * Tests Finder::findConfiguration
     */
    public function testFindConfigurationWithFile()
    {
        $finder = new Finder();
        $expected = PHPBU_TEST_FILES . '/conf/default-xml/phpbu.xml';

        $this->assertEquals($expected, $finder->findConfiguration(PHPBU_TEST_FILES . '/conf/default-xml/phpbu.xml'));
    }

    /**
     * Tests Finder::findConfiguration
     */
    public function testFindConfigurationInDirDotXml()
    {
        $finder = new Finder();
        $expected = PHPBU_TEST_FILES . '/conf/default-xml/phpbu.xml';

        $this->assertEquals($expected, $finder->findConfiguration(PHPBU_TEST_FILES . '/conf/default-xml'));
    }

    /**
     * Tests Finder::findConfiguration
     */
    public function testFindConfigurationInDirDotDist()
    {
        $finder = new Finder();
        $expected = PHPBU_TEST_FILES . '/conf/default-xml-dist/phpbu.xml.dist';

        $this->assertEquals($expected, $finder->findConfiguration(PHPBU_TEST_FILES . '/conf/default-xml-dist'));
    }
}
