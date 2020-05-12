<?php
namespace phpbu\App\Result;

use phpbu\App\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Backup test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class BackupTest extends TestCase
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
        $check  = new Configuration\Backup\Check('SizeMin', '10M');
        $backup = new Backup('name');

        $backup->checkAdd($check);
        $backup->checkFailed($check);

        $this->assertEquals(1, $backup->checkCountFailed());
        $this->assertEquals(1, $backup->checkCount());
    }

    /**
     * Test Crypt handling.
     */
    public function testCrypt()
    {
        $crypt  = new Configuration\Backup\Crypt('mcrypt', false);
        $backup = new Backup('name');

        $backup->cryptAdd($crypt);
        $backup->cryptFailed($crypt);
        $backup->cryptSkipped($crypt);

        $this->assertEquals(1, $backup->cryptCountFailed(), 'failed');
        $this->assertEquals(1, $backup->cryptCountSkipped(), 'skipped');
        $this->assertEquals(1, $backup->cryptCount(), 'executed');
    }

    /**
     * Test Sync handling.
     */
    public function testSync()
    {
        $sync   = new Configuration\Backup\Sync('rsync', false);
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
        $cleanup = new Configuration\Backup\Cleanup('capacity', false);
        $backup  = new Backup('name');

        $backup->cleanupAdd($cleanup);
        $backup->cleanupFailed($cleanup);
        $backup->cleanupSkipped($cleanup);

        $this->assertEquals(1, $backup->cleanupCountFailed());
        $this->assertEquals(1, $backup->cleanupCountSkipped());
        $this->assertEquals(1, $backup->cleanupCount());
    }
}
