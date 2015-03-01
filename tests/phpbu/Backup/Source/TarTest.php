<?php
namespace phpbu\Backup\Source;
use phpbu\Backup\Compressor;

/**
 * TarTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.5
 */
class TarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mysqldump
     *
     * @var \phpbu\Backup\Source\Tar
     */
    protected $tar;

    /**
     * Setup mysqldump
     */
    public function setUp()
    {
        $this->tar = new Tar();
        $this->tar->setBinary('tar');
    }

    /**
     * Clear mysqldump
     */
    public function tearDown()
    {
        $this->tar = null;
    }

    /**
     * Tests Tar::setUp
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupPathMissing()
    {
        $this->tar->setup(array());

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Tar::getExec
     */
    public function testDefault()
    {
        $target = $this->getTargetMock();
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $this->tar->setup(array('path' => 'src'));
        /** @var \phpbu\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -cf \'/tmp/backup.tar\' \'src\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Tar::getExec
     */
    public function testShowStdErr()
    {
        $target = $this->getTargetMock();
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $this->tar->setup(array('path' => 'src', 'showStdErr' => 'true'));
        /** @var \phpbu\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -cf \'/tmp/backup.tar\' \'src\'', $cmd);
    }

    /**
     * Tests Tar::getExec
     */
    public function testCompressorValid()
    {
        $target     = $this->getTargetMock();
        $compressor = Compressor::create('gzip');

        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');
        $target->method('getCompressor')->willReturn($compressor);

        $this->tar->setup(array('path' => 'src'));
        /** @var \phpbu\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -zcf \'/tmp/backup.tar\' \'src\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Tar::getExec
     */
    public function testCompressorInvalid()
    {
        $target     = $this->getTargetMock();
        $compressor = Compressor::create('zip');

        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');
        $target->method('getCompressor')->willReturn($compressor);

        $this->tar->setup(array('path' => 'src'));
        /** @var \phpbu\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -cf \'/tmp/backup.tar\' \'src\' 2> /dev/null', $cmd);
    }

    /**
     * Create Target Mock.
     *
     * @return \phpbu\Backup\Target
     */
    protected function getTargetMock()
    {
        $targetStub = $this->getMockBuilder('\\phpbu\\Backup\\Target')
            ->disableOriginalConstructor()
            ->getMock();

        $targetStub->method('getSize')->willReturn(1090);

        return $targetStub;
    }
}
