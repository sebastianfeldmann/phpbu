<?php
namespace phpbu\App\Configuration;

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
class FinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Finder::findConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFindConfigurationNoConfigInDir()
    {
        $finder = new Finder();
        $finder->findConfiguration(PHPBU_TEST_FILES . '/conf');
    }

    /**
     * Tests Finder::findConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFindConfigurationInvalidPath()
    {
        $finder = new Finder();
        $finder->findConfiguration(PHPBU_TEST_FILES . '/fooBarBaz');
    }

    /**
     * Tests Finder::findConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFindDefaultConfFail()
    {
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
