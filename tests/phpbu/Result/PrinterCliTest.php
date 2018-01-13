<?php
namespace phpbu\App\Result;

use phpbu\App\Configuration;

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
class PrinterCliTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Mail::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $events = PrinterCli::getSubscribedEvents();

        $this->assertTrue(array_key_exists('phpbu.debug', $events));
        $this->assertTrue(array_key_exists('phpbu.backup_start', $events));
        $this->assertTrue(array_key_exists('phpbu.check_start', $events));

        $this->assertEquals('onPhpbuEnd', $events['phpbu.app_end']);
    }

    /**
     * Tests PrinterCli::__construct
     */
    public function testConstructOk()
    {
        $printer = new PrinterCli(false, false, false);

        $this->assertTrue(!is_null($printer));
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
        $this->assertTrue(strpos($output, 'Runtime') !== false);
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
        $this->assertTrue(strpos($output, 'backup') !== false);
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
        $this->assertTrue(strpos($output, 'failed') !== false);
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
        $this->assertTrue(strpos($output, 'check') !== false);
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
        $this->assertTrue(strpos($output, 'failed') !== false);
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
        $this->assertTrue(strpos($output, 'crypt') !== false);
        $this->assertTrue(strpos($output, 'ok') !== false);
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
        $this->assertTrue(strpos($output, 'failed') !== false);
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
        $this->assertTrue(strpos($output, 'skipped') !== false);
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
        $this->assertTrue(strpos($output, 'sync') !== false);
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
        $this->assertTrue(strpos($output, 'failed') !== false);
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
        $this->assertTrue(strpos($output, 'skipped') !== false);
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
        $this->assertTrue(strpos($output, 'cleanup') !== false);
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
        $this->assertTrue(strpos($output, 'failed') !== false);
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
        $this->assertTrue(strpos($output, 'skipped') !== false);
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
        $this->assertTrue(strpos($output, 'foo') !== false);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultAllOk()
    {
        $printer = new PrinterCli(true, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);
        $backup  = $this->createMock(\phpbu\App\Result\Backup::class);
        $result->method('getBackups')->willReturn(array($backup));
        $result->method('getErrors')->willReturn(array());
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
        $this->assertTrue(strpos($output, 'OK') !== false);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultNoBackup()
    {
        $printer = new PrinterCli(false, true, false);
        $result  = $this->createMock(\phpbu\App\Result::class);

        $result->method('getBackups')->willReturn(array());
        $result->method('getErrors')->willReturn(array());

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'No backups executed') !== false);
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

        $result->method('getBackups')->willReturn(array($backup));
        $result->method('getErrors')->willReturn(array());
        $result->method('allOk')->willReturn(false);
        $result->method('wasSuccessful')->willReturn(true);
        $result->method('backupOkButSkipsOrFails')->willReturn(true);

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'skipped') !== false);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultFailure()
    {
        $printer = new PrinterCli(true, false, false);
        $result  = $this->createMock(\phpbu\App\Result::class);
        $e       = $this->createMock(\phpbu\App\Exception::class);
        $e->method('getMessage')->willReturn('foo');
        $e->method('getFile')->willReturn('foo.php');
        $e->method('getLine')->willReturn(1);
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

        $result->method('getBackups')->willReturn(array($backup));
        $result->method('getErrors')->willReturn(array($e));
        $result->method('allOk')->willReturn(false);
        $result->method('wasSuccessful')->willReturn(false);

        ob_start();
        $printer->printResult($result);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'FAILURE') !== false);
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
