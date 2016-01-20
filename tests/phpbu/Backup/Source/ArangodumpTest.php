<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;

/**
 * Arangodump Source Test
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
class ArangodumpTest extends CliTest
{
    /**
     * Arangodump
     *
     * @var \phpbu\App\Backup\Source\Arangodump
     */
    protected $arangodump;

    /**
     * Setup arangodump
     */
    public function setUp()
    {
        $this->arangodump = new Arangodump();
    }

    /**
     * Clear arangodump
     */
    public function tearDown()
    {
        $this->arangodump = null;
    }

    /**
     * Tests Arangodump::getExecutable
     */
    public function testDefault()
    {
        $expected = 'arangodump --output-directory \'./dir/dump\'';
        $target   = $this->getTargetMock('./dir/foo.dump');
        $path     = $this->getBinDir();
        $this->arangodump->setup(array('pathToArangodump' => $path));

        $executable = $this->arangodump->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Arangodump::getExecutable
     */
    public function testUser()
    {
        $expected = 'arangodump --server.username \'root\' --output-directory \'./dir/dump\'';
        $target   = $this->getTargetMock('./dir/foo.dump');
        $path     = $this->getBinDir();
        $this->arangodump->setup(array('pathToArangodump' => $path, 'username' => 'root'));

        $executable = $this->arangodump->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Arangodump::Executable
     */
    public function testCollections()
    {
        $expected = 'arangodump --collection \'collection1\' --collection \'collection2\' --output-directory \'./dir/dump\'';
        $target   = $this->getTargetMock('./dir/foo.dump');
        $path     = $this->getBinDir();
        $this->arangodump->setup(array('pathToArangodump' => $path, 'collections' => 'collection1,collection2'));

        $executable = $this->arangodump->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Arangodump::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock(__FILE__);
        $cliResult = $this->getCliResultMock(0, 'arangodump');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Arangodump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->arangodump->setup(array());
        $this->arangodump->setExecutable($exec);
        $status = $this->arangodump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Arangodump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock(__FILE__);
        $cliResult = $this->getCliResultMock(1, 'arangodump');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Arangodump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->arangodump->setup(array());
        $this->arangodump->setExecutable($exec);
        $this->arangodump->backup($target, $appResult);
    }
}
