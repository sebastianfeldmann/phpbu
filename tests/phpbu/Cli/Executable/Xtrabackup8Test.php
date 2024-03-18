<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * XtraBackup Executable Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0.10
 */
class Xtrabackup8Test extends TestCase
{
    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testDefault()
    {
        $expectedDump  = 'xtrabackup" --backup --target-dir=\'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;
        $xtra          = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testDataDir()
    {
        $expectedDump  = 'xtrabackup" --backup --datadir=\'/foo/bar\' --target-dir=\'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;
        $xtra          = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->dumpFrom('/foo/bar')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testFailNoDumpDir()
    {
        $this->expectException('phpbu\App\Exception');
        $xtra  = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->getCommand();
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testUser()
    {
        $expectedDump  = 'xtrabackup" --backup --user=\'root\' --target-dir=\'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;
        $xtra          = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->credentials('root')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testPassword()
    {
        $expectedDump  = 'xtrabackup" --backup --password=\'secret\' --target-dir=\'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;
        $xtra          = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->credentials('', 'secret')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testHost()
    {
        $expectedDump  = '"' . PHPBU_TEST_BIN . '/xtrabackup" --backup --host=\'example.com\' --target-dir=\'./dump\'';
        $expected      = $expectedDump;
        $xtra          = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->useHost('example.com')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testDatabases()
    {
        $expectedDump  = '"' . PHPBU_TEST_BIN . '/xtrabackup" --backup '
                       . '--databases=\'db1 db2 db3.table1\' --target-dir=\'./dump\'';
        $expected      = $expectedDump;
        $xtra          = new Xtrabackup8(PHPBU_TEST_BIN);
        $xtra->dumpDatabases(['db1', 'db2', 'db3.table1'])->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }
}
