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
     * Pgdump
     *
     * @var \phpbu\App\Backup\Source\Pgdump
     */
    protected $pgDump;

    /**
     * Setup pgdump
     */
    public function setUp()
    {
        $this->pgDump = new Pgdump();
    }

    /**
     * Clear pgdump
     */
    public function tearDown()
    {
        $this->pgDump = null;
    }

    /**
     * Tests Pgdump::getExecutable
     */
    public function testDefault()
    {
        $target = $this->getTargetMock('foo.sql');
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->pgDump->setup(['pathToPgdump' => $path]);

        $executable = $this->pgDump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/pg_dump -w --file=\'foo.sql\' --format=\'p\'', $cmd);
    }

    /**
     * Tests Pgdump::getExecutable
     */
    public function testDatabase()
    {
        $target = $this->getTargetMock('foo.sql');
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->pgDump->setup(['pathToPgdump' => $path, 'database' => 'myDatabase']);

        $executable = $this->pgDump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/pg_dump -w --dbname=\'myDatabase\' --file=\'foo.sql\' --format=\'p\'', $cmd);
    }

    /**
     * Tests Pgdump::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'pg_dump');
        $appResult = $this->getAppResultMock();
        $pgDump    = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Pgdump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $pgDump->expects($this->once())->method('run')->willReturn($cliResult);

        $path = realpath(__DIR__ . '/../../../_files/bin');
        $this->pgDump->setup(['pathTopgDump' => $path]);
        $this->pgDump->setExecutable($pgDump);
        $status = $this->pgDump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Pgdump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'pg_dump');
        $appResult = $this->getAppResultMock();
        $pgDump    = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\pgdump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $pgDump->expects($this->once())->method('run')->willReturn($cliResult);

        $path = realpath(__DIR__ . '/../../../_files/bin');
        $this->pgDump->setup(['pathToPgDump' => $path]);
        $this->pgDump->setExecutable($pgDump);
        $this->pgDump->backup($target, $appResult);
    }
}
