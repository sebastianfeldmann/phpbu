<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * ArangodumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class ArangodumpTest extends TestCase
{
    /**
     * Tests Arangodump::createCommand
     */
    public function testDefault()
    {
        $expected = PHPBU_TEST_BIN . '/arangodump --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommand
     */
    public function testNoDumpDir()
    {
        $this->expectException('phpbu\App\Exception');
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->getCommand();
    }

    /**
     * Tests Arangodump::createCommand
     */
    public function testUser()
    {
        $expected = PHPBU_TEST_BIN . '/arangodump --server.username \'root\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->credentials('root')->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testPassword()
    {
        $expected = PHPBU_TEST_BIN . '/arangodump --server.password \'secret\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->credentials('', 'secret')->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testEndpoint()
    {
        $expected = PHPBU_TEST_BIN
                  . '/arangodump --server.endpoint \'tcp://example.com:8529\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->useEndpoint('tcp://example.com:8529')->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testDatabase()
    {
        $expected = PHPBU_TEST_BIN . '/arangodump --server.database \'myDatabase\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->dumpDatabase('myDatabase')->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testCollections()
    {
        $expected = PHPBU_TEST_BIN
                  . '/arangodump --collection \'col1\' --collection \'col2\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->dumpCollections(['col1', 'col2'])->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testDisableAuthentication()
    {
        $expected = PHPBU_TEST_BIN
                  . '/arangodump --server.disable-authentication \'true\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->disableAuthentication(true)->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testIncludeSystemCollections()
    {
        $expected = PHPBU_TEST_BIN . '/arangodump --include-system-collections \'true\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->includeSystemCollections(true)->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }

    /**
     * Tests Arangodump::createCommandLine
     */
    public function testDumpData()
    {
        $expected = PHPBU_TEST_BIN . '/arangodump --dump-data \'true\' --output-directory \'./dump\'';
        $arango   = new Arangodump(PHPBU_TEST_BIN);
        $arango->dumpData(true)->dumpTo('./dump');

        $this->assertEquals($expected, $arango->getCommand());
    }
}
