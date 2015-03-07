<?php
namespace phpbu\App;
use phpbu\App\Backup\Check\Exception;

/**
 * Version test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.6
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Result::wasSuccessFul
     */
    public function testSuccessFullByDefault()
    {
        $result = new Result();

        $this->assertTrue($result->wasSuccessful(), 'should be successful by default');
        $this->assertTrue($result->allOk(), 'should be ok by default');
        $this->assertFalse($result->backupOkButSkipsOrFails(), 'nothing should be skipped');
    }

    /**
     * Tests minimal Result life cycle.
     */
    public function testBackupMinimal()
    {
        $backup = array('name' => 'test-backup');

        $result = new Result();
        $result->phpbuStart(array());
        $result->backupStart($backup);
        $result->backupEnd($backup);
        $result->phpbuEnd();

        $this->assertTrue($result->wasSuccessful(), 'should be successful');
        $this->assertTrue($result->allOk(), 'should be ok');
        $this->assertFalse($result->backupOkButSkipsOrFails(), 'nothing should be skipped');
    }

    /**
     * Tests max Result life cycle.
     */
    public function testBackupMaximalAllOk()
    {
        $backup  = array('name' => 'test-backup');
        $check   = array('type' => 'sizemin');
        $sync    = array('type' => 'rsync');
        $cleanup = array('type' => 'capacity');

        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
                         ->disableOriginalConstructor()
                         ->getMock();
        $result->addListener($listener);
        $result->phpbuStart(array());
        $result->backupStart($backup);
        $result->checkStart($check);
        $result->checkEnd($check);
        $result->syncStart($sync);
        $result->syncEnd($sync);
        $result->cleanupStart($cleanup);
        $result->cleanupEnd($cleanup);
        $result->backupEnd($backup);
        $result->phpbuEnd();

        $this->assertTrue($result->wasSuccessful(), 'should be successful');
        $this->assertTrue($result->allOk(), 'should be ok');
        $this->assertFalse($result->backupOkButSkipsOrFails(), 'nothing should be skipped');
        $this->assertEquals(0, $result->backupsFailedCount());
        $this->assertEquals(0, $result->checksFailedCount());
        $this->assertEquals(0, $result->syncsFailedCount());
        $this->assertEquals(0, $result->syncsSkippedCount());
        $this->assertEquals(0, $result->cleanupsFailedCount());
        $this->assertEquals(0, $result->cleanupsSkippedCount());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, count($result->getErrors()));
        $this->assertEquals(1, count($result->getBackups()));
    }

    /**
     * Tests max Result life cycle with skips.
     */
    public function testBackupMaximalWithSkips()
    {
        $backup  = array('name' => 'test-backup');
        $check   = array('type' => 'sizemin');
        $sync    = array('type' => 'rsync');
        $cleanup = array('type' => 'capacity');

        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
                         ->disableOriginalConstructor()
                         ->getMock();
        $result->addListener($listener);
        $result->phpbuStart(array());
        $result->backupStart($backup);
        $result->checkStart($check);
        $result->checkEnd($check);
        $result->syncSkipped($sync);
        $result->cleanupSkipped($cleanup);
        $result->backupEnd($backup);
        $result->phpbuEnd();

        $this->assertTrue($result->wasSuccessful(), 'should be successful');
        $this->assertFalse($result->allOk(), 'should be ok');
        $this->assertTrue($result->backupOkButSkipsOrFails(), 'sync and cleanup should be skipped');
        $this->assertEquals(0, $result->syncsFailedCount());
        $this->assertEquals(1, $result->syncsSkippedCount());
        $this->assertEquals(0, $result->cleanupsFailedCount());
        $this->assertEquals(1, $result->cleanupsSkippedCount());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, count($result->getErrors()));
        $this->assertEquals(1, count($result->getBackups()));
    }

    /**
     * Tests max Result life cycle with errors.
     */
    public function testBackupMaximalWithErrors()
    {
        $backup  = array('name' => 'test-backup');
        $check   = array('type' => 'sizemin');
        $sync    = array('type' => 'rsync');
        $cleanup = array('type' => 'capacity');

        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
                         ->disableOriginalConstructor()
                         ->getMock();
        $result->addListener($listener);
        $result->phpbuStart(array());
        $result->backupStart($backup);
        $result->checkStart($check);
        $result->checkEnd($check);
        $result->syncStart($sync);
        $result->addError(new Exception('failed'));
        $result->syncFailed($sync);
        $result->cleanupStart($cleanup);
        $result->addError(new Exception('failed'));
        $result->cleanupFailed($sync);
        $result->backupEnd($backup);
        $result->phpbuEnd();

        $this->assertTrue($result->wasSuccessful(), 'should be successful');
        $this->assertFalse($result->allOk(), 'should be ok');
        $this->assertTrue($result->backupOkButSkipsOrFails(), 'sync and cleanup should be skipped');
        $this->assertEquals(1, $result->syncsFailedCount());
        $this->assertEquals(0, $result->syncsSkippedCount());
        $this->assertEquals(1, $result->cleanupsFailedCount());
        $this->assertEquals(0, $result->cleanupsSkippedCount());
        $this->assertEquals(2, $result->errorCount());
        $this->assertEquals(2, count($result->getErrors()));
        $this->assertEquals(1, count($result->getBackups()));
    }

    /**
     * Tests Backup failed.
     */
    public function testBackupFailed()
    {
        $backup   = array('name' => 'test-backup');
        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
                         ->disableOriginalConstructor()
                         ->getMock();
        $result->addListener($listener);
        $result->phpbuStart(array());
        $result->backupStart($backup);
        $result->addError(new Exception('failed'));
        $result->backupFailed($backup);
        $result->backupEnd($backup);
        $result->phpbuEnd();

        $this->assertFalse($result->wasSuccessful(), 'should be successful');
        $this->assertFalse($result->allOk(), 'should be ok');
        $this->assertEquals(1, $result->backupsFailedCount());
        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(1, count($result->getErrors()));
        $this->assertEquals(1, count($result->getBackups()));
    }

    /**
     * Tests Check failed.
     */
    public function testBackupFailedOnFailedCheck()
    {
        $backup   = array('name' => 'test-backup');
        $check    = array('type' => 'sizemin');
        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
            ->disableOriginalConstructor()
            ->getMock();
        $result->addListener($listener);
        $result->phpbuStart(array());
        $result->backupStart($backup);
        $result->backupEnd($backup);
        $result->checkStart($check);
        $result->addError(new Exception('failed'));
        $result->checkFailed($check);
        $result->backupFailed($backup);
        $result->backupEnd($backup);
        $result->phpbuEnd();

        $this->assertFalse($result->wasSuccessful(), 'should be successful');
        $this->assertFalse($result->allOk(), 'should be ok');
        $this->assertEquals(1, $result->backupsFailedCount());
        $this->assertEquals(1, $result->checksFailedCount());
        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(1, count($result->getErrors()));
        $this->assertEquals(1, count($result->getBackups()));
    }

    /**
     * Tests debug.
     */
    public function testDebug()
    {
        $backup   = array('name' => 'test-backup');
        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
                         ->disableOriginalConstructor()
                         ->getMock();
        $listener->expects($this->once())->method('debug');

        $result->addListener($listener);
        $result->phpbuStart(array());
        $result->debug('debug');
        $result->backupStart($backup);
        $result->backupEnd($backup);
        $result->phpbuEnd();
    }

    /**
     * Tests removeListener.
     */
    public function testRemoveListener()
    {
        $result   = new Result();
        $listener = $this->getMockBuilder('\\phpbu\\App\\Result\\PrinterCli')
                         ->disableOriginalConstructor()
                         ->getMock();

        $listener->expects($this->exactly(0))->method('debug');

        $result->addListener($listener);
        $result->removeListener($listener);

        $result->debug('debug');
    }
}
