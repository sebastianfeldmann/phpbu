<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;

/**
 * MongodumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class MongodumpTest extends CliTest
{
    /**
     * Mongodump
     *
     * @var \phpbu\App\Backup\Source\Mongodump
     */
    protected $mongodump;

    /**
     * Setup Mongodump
     */
    public function setUp()
    {
        $this->mongodump = new Mongodump();
    }

    /**
     * Clear Mongodump
     */
    public function tearDown()
    {
        $this->mongodump = null;
    }

    /**
     * Tests Mongodump::getExecutable
     */
    public function testDefault()
    {
        $target = $this->getTargetMock(__FILE__);
        $path   = $this->getBinDir();
        $this->mongodump->setup(array('pathToMongodump' => $path));

        $executable = $this->mongodump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/mongodump --out \'' . __DIR__ . '/dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock(__FILE__);
        $cliResult = $this->getCliResultMock(0, 'mongodump');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Mongodump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->mongodump->setup(array());
        $this->mongodump->setExecutable($exec);
        $status = $this->mongodump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Mongodump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'mongodump');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Mongodump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->mongodump->setup(array());
        $this->mongodump->setExecutable($exec);
        $this->mongodump->backup($target, $appResult);
    }
}
