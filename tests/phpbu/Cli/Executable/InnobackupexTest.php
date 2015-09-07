<?php
namespace phpbu\App\Cli\Executable;

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
class InnobackupexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests XtraBackup::getExec
     */
    public function testDefault()
    {
        $expectedDump  = 'innobackupex --no-timestamp \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFailNoDumpDir()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $xtra  = new Innobackupex($path);
        $xtra->getCommandLine();
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testShowStdErr()
    {
        $expectedDump  = 'innobackupex --no-timestamp \'./dump\'';
        $expectedApply = 'innobackupex --apply-log \'./dump\'';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->dumpTo('./dump')->showStdErr(true);

        $this->assertEquals($expected, $xtra->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testUser()
    {
        $expectedDump  = 'innobackupex --no-timestamp --user=\'root\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->credentials('root')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testPassword()
    {
        $expectedDump  = 'innobackupex --no-timestamp --password=\'secret\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->credentials(null, 'secret')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testHost()
    {
        $expectedDump  = 'innobackupex --no-timestamp --host=\'example.com\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->useHost('example.com')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testDatabases()
    {
        $expectedDump  = 'innobackupex --no-timestamp --databases=\'db1 db2 db3.table1\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->dumpDatabases(array('db1', 'db2', 'db3.table1'))->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testInclude()
    {
        $expectedDump  = 'innobackupex --no-timestamp --include=\'^myDatabase[.]myTable\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $path          = realpath(__DIR__ . '/../../../_files/bin');
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $xtra          = new Innobackupex($path);
        $xtra->including('^myDatabase[.]myTable')->dumpTo('./dump');

        $this->assertEquals($expected, $xtra->getCommandLine());
    }
}
