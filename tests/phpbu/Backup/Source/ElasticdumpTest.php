<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;

/**
 * Elasticdump Source Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class ElasticdumpTest extends CliTest
{
    /**
     * Elasticdump
     *
     * @var \phpbu\App\Backup\Source\Elasticdump
     */
    protected $elasticdump;

    /**
     * Setup elasticdump
     */
    public function setUp()
    {
        $this->elasticdump = new Elasticdump();
    }

    /**
     * Clear elasticdump
     */
    public function tearDown()
    {
        $this->elasticdump = null;
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testDefault()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/\' --output=\'backup.json\'';
        $target   = $this->getTargetMock('backup.json');
        $path     = $this->getBinDir();
        $this->elasticdump->setup(array('pathToElasticdump' => $path));

        $executable = $this->elasticdump->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testUser()
    {
        $expected = 'elasticdump --input=\'http://root@localhost:9200/\' --output=\'backup.json\'';
        $target   = $this->getTargetMock('backup.json');
        $path     = $this->getBinDir();
        $this->elasticdump->setup(array('pathToElasticdump' => $path, 'user' => 'root'));

        $executable = $this->elasticdump->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Elasticdump::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock('backup.json');
        $cliResult = $this->getCliResultMock(0, 'elasticdump');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Elasticdump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->elasticdump->setup(array());
        $this->elasticdump->setExecutable($exec);
        $this->elasticdump->backup($target, $appResult);
    }

    /**
     * Tests Elasticdump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock('backup.json');
        $cliResult = $this->getCliResultMock(1, 'elasticdump');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Elasticdump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->elasticdump->setup(array());
        $this->elasticdump->setExecutable($exec);
        $this->elasticdump->backup($target, $appResult);
    }
}
