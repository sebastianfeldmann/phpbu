<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * XtraBackup Executable Test
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
class InnobackupexTest extends TestCase
{
    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testDefault()
    {
        $expectedDump  = 'innobackupex --no-timestamp \'./dump\'';
        $expectedApply = 'innobackupex --apply-log \'./dump\'';
        $expected      = '(' . PHPBU_TEST_BIN . '/'
                       . $expectedDump . ' && ' . PHPBU_TEST_BIN
                       . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testDataDir()
    {
        $expectedDump  = 'innobackupex --no-timestamp --datadir=\'/foo/bar\' \'./dump\'';
        $expectedApply = 'innobackupex --apply-log \'./dump\'';
        $expected      = '(' . PHPBU_TEST_BIN . '/' . $expectedDump . ' && '
                       . PHPBU_TEST_BIN . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->dumpFrom('/foo/bar')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests Mongodump::createCommandLine
     */
    public function testFailNoDumpDir()
    {
        $this->expectException('phpbu\App\Exception');
        $xtra  = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->getCommand();
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testUser()
    {
        $expectedDump  = 'innobackupex --no-timestamp --user=\'root\' \'./dump\'';
        $expectedApply = 'innobackupex --apply-log \'./dump\'';
        $expected      = '(' . PHPBU_TEST_BIN . '/' . $expectedDump
                       . ' && ' . PHPBU_TEST_BIN . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->credentials('root')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testPassword()
    {
        $expectedDump  = 'innobackupex --no-timestamp --password=\'secret\' \'./dump\'';
        $expectedApply = 'innobackupex --apply-log \'./dump\'';
        $expected      = '(' . PHPBU_TEST_BIN . '/' . $expectedDump . ' && '
                       . PHPBU_TEST_BIN . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->credentials('', 'secret')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testHost()
    {
        $expectedDump  = PHPBU_TEST_BIN . '/innobackupex --no-timestamp --host=\'example.com\' \'./dump\'';
        $expectedApply = PHPBU_TEST_BIN . '/innobackupex --apply-log \'./dump\'';
        $expected      = '(' . $expectedDump . ' && ' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->useHost('example.com')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testDatabases()
    {
        $expectedDump  = PHPBU_TEST_BIN . '/innobackupex --no-timestamp --databases=\'db1 db2 db3.table1\' \'./dump\'';
        $expectedApply = PHPBU_TEST_BIN . '/innobackupex --apply-log \'./dump\'';
        $expected      = '(' . $expectedDump . ' && ' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->dumpDatabases(['db1', 'db2', 'db3.table1'])->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }

    /**
     * Tests XtraBackup::createCommandLine
     */
    public function testInclude()
    {
        $expectedDump  = PHPBU_TEST_BIN . '/innobackupex --no-timestamp --include=\'^myDatabase[.]myTable\' \'./dump\'';
        $expectedApply = PHPBU_TEST_BIN . '/innobackupex --apply-log \'./dump\'';
        $expected      = '(' . $expectedDump . ' && ' . $expectedApply . ')';
        $xtra          = new Innobackupex(PHPBU_TEST_BIN);
        $xtra->including('^myDatabase[.]myTable')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommand());
    }
}
