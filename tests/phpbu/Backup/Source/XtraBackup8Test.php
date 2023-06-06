<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * XtraBackup Source Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XtraBackup8Test extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests XtraBackup::getExecutable
     */
    public function testDefault()
    {
        $target = $this->createTargetMock('./foo.dump');

        $xtrabackup = new XtraBackup8();
        $xtrabackup->setup(['pathToXtraBackup' => PHPBU_TEST_BIN]);

        $executable    = $xtrabackup->getExecutable($target);
        $expectedDump  = 'xtrabackup" --backup \'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests XtraBackup::getExecutable
     */
    public function testDataDir()
    {
        $target = $this->createTargetMock('./foo.dump');

        $xtrabackup = new XtraBackup8();
        $xtrabackup->setup(['pathToXtraBackup' => PHPBU_TEST_BIN, 'dataDir' => '/x/mysql']);

        $executable    = $xtrabackup->getExecutable($target);
        $expectedDump  = 'xtrabackup" --backup --datadir=\'/x/mysql\' \'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests XtraBackup::getExecutable
     */
    public function testDatabases()
    {
        $target = $this->createTargetMock('./foo.dump');

        $xtrabackup = new XtraBackup8();
        $xtrabackup->setup(['pathToXtraBackup' => PHPBU_TEST_BIN, 'databases' => 'db1,db2,db3.table1']);

        $executable    = $xtrabackup->getExecutable($target);
        $expectedDump  = 'xtrabackup" --backup --databases=\'db1 db2 db3.table1\' \'./dump\'';
        $expected      = '"' . PHPBU_TEST_BIN . '/' . $expectedDump;

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests XtraBackup::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')->willReturn($this->getRunnerResultMock(0, 'xtrabackup'));

        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $xtrabackup = new XtraBackup8($runner);
        $xtrabackup->setup(['pathToXtraBackup' => PHPBU_TEST_BIN]);

        $status = $xtrabackup->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests XtraBackup::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');

        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'xtrabackup'));

        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $xtrabackup = new XtraBackup8($runner);
        $xtrabackup->setup(['pathToXtraBackup' => PHPBU_TEST_BIN]);

        $xtrabackup->backup($target, $appResult);
    }
}
