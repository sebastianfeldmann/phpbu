<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;

/**
 * Pgdump Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class PgdumpTest extends CliTest
{
    /**
     * Tests Pgdump::getExecutable
     */
    public function testDefault()
    {
        $target = $this->getTargetMock('foo.sql');
        $pgDump = new Pgdump();
        $pgDump->setup(['pathToPgdump' => PHPBU_TEST_BIN]);

        $executable = $pgDump->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --file=\'foo.sql\' --format=\'p\'',
            $executable->getCommand()
        );
    }

    /**
     * Tests Pgdump::getExecutable
     */
    public function testDatabase()
    {
        $target = $this->getTargetMock('foo.sql');
        $pgDump = new Pgdump();
        $pgDump->setup(['pathToPgdump' => PHPBU_TEST_BIN, 'database' => 'myDatabase']);

        $executable = $pgDump->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'myDatabase\' --file=\'foo.sql\' --format=\'p\'',
            $executable->getCommand()
        );
    }

    /**
     * Tests Pgdump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'pg_dump'));

        $target    = $this->getTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $pgDump = new Pgdump($runner);
        $pgDump->setup(['pathToPgdump' => PHPBU_TEST_BIN]);
        $status = $pgDump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Pgdump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'pg_dump'));

        $target    = $this->getTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $pgDump = new Pgdump($runner);
        $pgDump->setup(['pathToPgdump' => PHPBU_TEST_BIN]);
        $pgDump->backup($target, $appResult);
    }
}
