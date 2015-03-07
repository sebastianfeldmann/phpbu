<?php
namespace phpbu\App\Result;

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
class BackupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Backup::wasSuccessFul
     */
    public function testSuccessFullByDefault()
    {
        $backup = new Backup('name');

        $this->assertTrue($backup->wasSuccessful(), 'should be successful by default');
        $this->assertTrue($backup->allOk(), 'should be ok by default');
        $this->assertFalse($backup->okButSkipsOrFails(), 'nothing should be skipped');
    }

    /**
     * Tests Backup::getName
     */
    public function testGetName()
    {
        $backup = new Backup('name');

        $this->assertEquals('name', $backup->getName());
    }

    /**
     * Test Check handling.
     */
    public function testCheck()
    {
        $check  = array('type' => 'minsize');
        $backup = new Backup('name');

        $backup->checkAdd($check);
        $backup->checkFailed($check);

        $this->assertEquals(1, $backup->checkCountFailed());
        $this->assertEquals(1, $backup->checkCount());
    }

    /**
     * Test Check handling.
     */
    public function testSync()
    {
        $sync   = array('type' => 'rsync');
        $backup = new Backup('name');

        $backup->syncAdd($sync);
        $backup->syncFailed($sync);
        $backup->syncSkipped($sync);

        $this->assertEquals(1, $backup->syncCountFailed());
        $this->assertEquals(1, $backup->syncCountSkipped());
        $this->assertEquals(1, $backup->syncCount());
    }

    /**
     * Test Cleanup handling.
     */
    public function testCleanup()
    {
        $cleanup = array('type' => 'capacity');
        $backup  = new Backup('name');

        $backup->cleanupAdd($cleanup);
        $backup->cleanupFailed($cleanup);
        $backup->cleanupSkipped($cleanup);

        $this->assertEquals(1, $backup->cleanupCountFailed());
        $this->assertEquals(1, $backup->cleanupCountSkipped());
        $this->assertEquals(1, $backup->cleanupCount());
    }
}
