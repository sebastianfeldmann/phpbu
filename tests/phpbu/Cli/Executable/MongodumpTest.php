<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * Mongodump Executable Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class MongodumpTest extends TestCase
{
    /**
     * Tests Mongodump::createCommandLine
     */
    public function testDefault()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump');

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testFailNoDumpDir()
    {
        $this->expectException('phpbu\App\Exception');
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->getCommand();
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testUser()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->credentials('root');

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --username \'root\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testPassword()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->credentials('', 'secret');

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --password \'secret\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testHost()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->useHost('example.com');

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --host \'example.com\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testDatabases()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->dumpDatabases(['db1', 'db2']);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --db \'db1\' --db \'db2\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testCollections()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->dumpCollections(['collection1', 'collection2']);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump'
            . '\' --collection \'collection1\' --collection \'collection2\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testIPv6()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->useIpv6(true);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --ipv6',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testExcludeCollections()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->excludeCollections(['col1', 'col2']);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --excludeCollection \'col1\' \'col2\'',
            $mongo->getCommand()
        );
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testExcludeCollectionsWithPrefix()
    {
        $mongo = new Mongodump(PHPBU_TEST_BIN);
        $mongo->dumpToDirectory('./dump')->excludeCollectionsWithPrefix(['pre1', 'pre2']);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mongodump --out \'./dump' . '\' --excludeCollectionWithPrefix \'pre1\' \'pre2\'',
            $mongo->getCommand()
        );
    }
}
