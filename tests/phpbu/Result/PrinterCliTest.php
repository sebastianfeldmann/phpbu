<?php
namespace phpbu\App\Result;

use phpbu\App\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * PrinterCli test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class PrinterCliTest extends TestCase
{
    /**
     * Tests Mail::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $events = PrinterCli::getSubscribedEvents();

        $this->assertArrayHasKey('phpbu.debug', $events);
        $this->assertArrayHasKey('phpbu.backup_start', $events);
        $this->assertArrayHasKey('phpbu.check_start', $events);

        $this->assertEquals('onPhpbuEnd', $events['phpbu.app_end']);
    }

    /**
     * Tests PrinterCli::__construct
     */
    public function testConstructOk()
    {
        $printer = new PrinterCli(false, false, false);

        $this->assertNotNull($printer);
    }

    /**
     * Tests PrinterCli::phpbuStart
     */
    public function testPhpbuStart()
    {
        $printer = new PrinterCli(false, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->once())
               ->method('getErrors')
               ->willReturn([]);
        $result->method('getBackups')
               ->willReturn([]);

        $configuration = $this->createMock(\phpbu\App\Configuration::class);
        $configuration->method('getFilename')
                      ->willReturn('/tmp/TestConfig.xml');

        ob_start();
        $printer->onPhpbuStart($this->getEventMock('App\\Start', $configuration));
        $printer->onPhpbuEnd($this->getEventMock('App\\End', $result));
        $output = ob_get_clean();
    }

    /**
     * Tests PrinterCli::phpbuStart
     */
    public function testPhpbuStarVerbose()
    {
        $printer = new PrinterCli(true, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);

        $configuration = $this->createMock(\phpbu\App\Configuration::class);
        $configuration->method('getFilename')->willReturn('/tmp/TestConfig.xml');

        ob_start();
        $printer->onPhpbuStart($this->getEventMock('App\\Start', $configuration));
        $output = ob_get_clean();
        $this->assertStringContainsString('Runtime', $output);
    }

    /**
     * Tests PrinterCli::backupStart
     */
    public function testBackupStart()
    {
        $source = new Configuration\Backup\Source('mysqldump');
        $backup = new Configuration\Backup('dummy', false);
        $backup->setSource($source);

        $printer = new PrinterCli(false, false, false);

        ob_start();
        $printer->onBackupStart($this->getEventMock('Backup\\Start', $backup));
        $printer->onBackupEnd($this->getEventMock('Backup\\End', $backup));
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::backupStart
     */
    public function testBackupStartDebug()
    {
        $source = new Configuration\Backup\Source('mysqldump');
        $backup = new Configuration\Backup('dummy', false);
        $backup->setSource($source);

        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onBackupStart($this->getEventMock('Backup\\Start', $backup));
        $printer->onBackupEnd($this->getEventMock('Backup\\End', $backup));
        $output = ob_get_clean();
        $this->assertStringContainsString('backup', $output);
    }

    /**
     * Tests PrinterCli::backupStart
     */
    public function testBackupFailed()
    {
        $source = new Configuration\Backup\Source('mysqldump');
        $backup = new Configuration\Backup('dummy', false);
        $backup->setSource($source);

        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onBackupStart($this->getEventMock('Backup\\Start', $backup));
        $printer->onBackupFailed($this->getEventMock('Backup\\Failed', $backup));
        $output = ob_get_clean();
        $this->assertStringContainsString('failed', $output);
    }

    /**
     * Tests PrinterCli::checkStart
     */
    public function testCheckStart()
    {
        $check   = new Configuration\Backup\Check('foo', 'bar');
        $printer = new PrinterCli(false, false, false);

        ob_start();
        $printer->onCheckStart($this->getEventMock('Check\\Start', $check));
        $printer->onCheckEnd($this->getEventMock('Check\\End', $check));
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::checkStart
     */
    public function testCheckStartDebug()
    {
        $check   = new Configuration\Backup\Check('TestType', 'TestValue');
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCheckStart($this->getEventMock('Check\\Start', $check));
        $printer->onCheckEnd($this->getEventMock('Check\\End', $check));
        $output = ob_get_clean();
        $this->assertStringContainsString('check', $output);
    }

    /**
     * Tests PrinterCli::checkFailed
     */
    public function testCheckFailed()
    {
        $check   = new Configuration\Backup\Check('TestType', 'TestValue');
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCheckStart($this->getEventMock('Check\\Start', $check));
        $printer->onCheckFailed($this->getEventMock('Check\\Failed', $check));
        $output = ob_get_clean();
        $this->assertStringContainsString('failed', $output);
    }


    /**
     * Tests PrinterCli::cryptStart
     */
    public function testCryptStartEnd()
    {
        $crypt   = new Configuration\Backup\Crypt('TestType', false);
        $printer = new PrinterCli(false, false, false);

        ob_start();
        $printer->onCryptStart($this->getEventMock('Crypt\\Start', $crypt));
        $printer->onCryptEnd($this->getEventMock('Crypt\\End', $crypt));
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::cryptStart
     */
    public function testCryptStartEndDebug()
    {
        $crypt   = new Configuration\Backup\Crypt('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCryptStart($this->getEventMock('Crypt\\Start', $crypt));
        $printer->onCryptEnd($this->getEventMock('Crypt\\End', $crypt));
        $output = ob_get_clean();
        $this->assertStringContainsString('crypt', $output);
        $this->assertStringContainsString('ok', $output);
    }

    /**
     * Tests PrinterCli::cryptFailed
     */
    public function testCryptFailed()
    {
        $crypt   = new Configuration\Backup\Crypt('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCryptStart($this->getEventMock('Crypt\\Start', $crypt));
        $printer->onCryptFailed($this->getEventMock('Crypt\\Failed', $crypt));
        $output = ob_get_clean();
        $this->assertStringContainsString('failed', $output);
    }

    /**
     * Tests PrinterCli::cryptSkipped
     */
    public function testCryptSkipped()
    {
        $crypt   = new Configuration\Backup\Crypt('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCryptStart($this->getEventMock('Crypt\\Start', $crypt));
        $printer->onCryptSkipped($this->getEventMock('Crypt\\Skipped', $crypt));
        $output = ob_get_clean();
        $this->assertStringContainsString('skipped', $output);
    }

    /**
     * Tests PrinterCli::syncStart
     */
    public function testSyncStart()
    {
        $sync    = new Configuration\Backup\Sync('TestType', false);
        $printer = new PrinterCli(false, false, false);

        ob_start();
        $printer->onSyncStart($this->getEventMock('Sync\\Start', $sync));
        $printer->onSyncEnd($this->getEventMock('Sync\\End', $sync));
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::syncStart
     */
    public function testSyncStartDebug()
    {
        $sync    = new Configuration\Backup\Sync('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onSyncStart($this->getEventMock('Sync\\Start', $sync));
        $printer->onSyncEnd($this->getEventMock('Sync\\End', $sync));
        $output = ob_get_clean();
        $this->assertStringContainsString('sync', $output);
    }

    /**
     * Tests PrinterCli::syncFailed
     */
    public function testSyncFailed()
    {
        $sync    = new Configuration\Backup\Sync('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onSyncStart($this->getEventMock('Sync\\Start', $sync));
        $printer->onSyncFailed($this->getEventMock('Sync\\Failed', $sync));
        $output = ob_get_clean();
        $this->assertStringContainsString('failed', $output);
    }

    /**
     * Tests PrinterCli::syncSkipped
     */
    public function testSyncSkipped()
    {
        $sync    = new Configuration\Backup\Sync('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onSyncStart($this->getEventMock('Sync\\Start', $sync));
        $printer->onSyncSkipped($this->getEventMock('Sync\\Skipped', $sync));
        $output = ob_get_clean();
        $this->assertStringContainsString('skipped', $output);
    }

    /**
     * Tests PrinterCli::cleanupStart
     */
    public function testCleanupStart()
    {
        $cleanup = new Configuration\Backup\Cleanup('TestType', false);
        $printer = new PrinterCli(false, false, false);

        ob_start();
        $printer->onCleanupStart($this->getEventMock('Cleanup\\Start', $cleanup));
        $printer->onCleanupEnd($this->getEventMock('Cleanup\\End', $cleanup));
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::cleanupStart
     */
    public function testCleanupStartDebug()
    {
        $cleanup = new Configuration\Backup\Cleanup('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCleanupStart($this->getEventMock('Cleanup\\Start', $cleanup));
        $printer->onCleanupEnd($this->getEventMock('Cleanup\\End', $cleanup));
        $output = ob_get_clean();
        $this->assertStringContainsString('cleanup', $output);
    }

    /**
     * Tests PrinterCli::cleanupFailed
     */
    public function testCleanupFailed()
    {
        $cleanup = new Configuration\Backup\Cleanup('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCleanupStart($this->getEventMock('Cleanup\\Start', $cleanup));
        $printer->onCleanupFailed($this->getEventMock('Cleanup\\Failed', $cleanup));
        $output = ob_get_clean();
        $this->assertStringContainsString('failed', $output);
    }

    /**
     * Tests PrinterCli::cleanupSkipped
     */
    public function testCleanupSkipped()
    {
        $cleanup = new Configuration\Backup\Cleanup('TestType', false);
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onCleanupStart($this->getEventMock('Cleanup\\Start', $cleanup));
        $printer->onCleanupSkipped($this->getEventMock('Cleanup\\Skipped', $cleanup));
        $output = ob_get_clean();
        $this->assertStringContainsString('skipped', $output);
    }

    /**
     * Tests PrinterCli::debug
     */
    public function testDebug()
    {
        $printer = new PrinterCli(false, false, true);

        ob_start();
        $printer->onDebug($this->getEventMock('Debug', 'foo'));
        $output = ob_get_clean();
        $this->assertStringContainsString('foo', $output);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultAllOk()
    {
        $printer = new PrinterCli(true, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);
        $backup  = $this->createMock(\phpbu\App\Result\Backup::class);
        $result->method('getBackups')->willReturn([$backup]);
        $result->method('getErrors')->willReturn([]);
        $result->method('allOk')->willReturn(true);

        $backup->method('allOk')->willReturn(true);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(0);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(0);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertStringContainsString('OK', $output);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultNoBackup()
    {
        $printer = new PrinterCli(false, true, false);
        $result  = $this->createMock(\phpbu\App\Result::class);

        $result->method('getBackups')->willReturn([]);
        $result->method('getErrors')->willReturn([]);

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertStringContainsString('No backups executed', $output);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultSkipped()
    {
        $printer = new PrinterCli(true, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);

        $backup  = $this->createMock(\phpbu\App\Result\Backup::class);

        $backup->method('allOk')->willReturn(false);
        $backup->method('wasSuccessful')->willReturn(true);
        $backup->method('okButSkipsOrFails')->willReturn(true);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(0);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(0);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        $result->method('getBackups')->willReturn([$backup]);
        $result->method('getErrors')->willReturn([]);
        $result->method('allOk')->willReturn(false);
        $result->method('wasSuccessful')->willReturn(true);
        $result->method('backupOkButSkipsOrFails')->willReturn(true);

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertStringContainsString('skipped', $output);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultFailure()
    {
        $printer = new PrinterCli(true, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);
        $e       = new \phpbu\App\Exception('foo');
        $backup  = $this->createMock(\phpbu\App\Result\Backup::class);

        $backup->method('allOk')->willReturn(false);
        $backup->method('wasSuccessful')->willReturn(false);
        $backup->method('okButSkipsOrFails')->willReturn(false);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(0);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(0);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        $result->method('getBackups')->willReturn([$backup]);
        $result->method('getErrors')->willReturn([$e]);
        $result->method('allOk')->willReturn(false);
        $result->method('wasSuccessful')->willReturn(false);

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertStringContainsString('FAILURE', $output);
    }

    /**
     * Create Event Mock.
     *
     * @param  string $type
     * @param  mixed  $arg
     * @return mixed
     */
    public function getEventMock($type, $arg)
    {
        $e = $this->createMock('\\phpbu\\App\\Event\\' . $type);
        switch ($type) {
            case 'App\\End':
                $e->method('getResult')->willReturn($arg);
                break;
            case 'Debug':
                $e->method('getMessage')->willReturn($arg);
                break;
            default:
                $e->method('getConfiguration')->willReturn($arg);
                break;
        }
        return $e;
    }
}
