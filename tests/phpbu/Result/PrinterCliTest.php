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
 * @since      Class available since Release 1.1.6
 */
class PrinterCliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests PrinterCli::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentVerbose()
    {
        $printer = new PrinterCli(null, 'foo', 'bar', 'baz');
    }

    /**
     * Tests PrinterCli::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentColors()
    {
        $printer = new PrinterCli(null, false, 'bar', 'baz');
    }

    /**
     * Tests PrinterCli::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentDebug()
    {
        $printer = new PrinterCli(null, false, false, 'baz');
    }

    /**
     * Tests PrinterCli::__construct
     */
    public function testConstructOk()
    {
        $printer = new PrinterCli(null, false, false, false);

        $this->assertTrue(!is_null($printer));
    }

    /**
     * Tests PrinterCli::phpbuStart
     */
    public function testPhpbuStart()
    {
        $printer = new PrinterCli(null, false, false, false);
        $result  = $this->getMockBuilder('\\phpbu\\App\\Result')
                        ->disableOriginalConstructor()
                        ->getMock();

        ob_start();
        $printer->phpbuStart(array('configuration' => 'TestConfig.xml'));
        $printer->phpbuEnd($result);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'phpbu') !== false);
        $this->assertTrue(strpos($output, 'TestConfig.xml') !== false);
    }

    /**
     * Tests PrinterCli::backupStart
     */
    public function testBackupStart()
    {
        $backup  = array('source' => array('type' => 'mysqldump'));
        $printer = new PrinterCli(null, false, false, false);

        ob_start();
        $printer->backupStart($backup);
        $printer->backupEnd($backup);
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::backupStart
     */
    public function testBackupStartDebug()
    {
        $backup  = array('source' => array('type' => 'mysqldump'));
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->backupStart($backup);
        $printer->backupEnd($backup);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'create backup') !== false);
    }

    /**
     * Tests PrinterCli::backupStart
     */
    public function testBackupFailed()
    {
        $backup  = array('source' => array('type' => 'mysqldump'));
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->backupStart($backup);
        $printer->backupFailed($backup);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'failed') !== false);
    }

    /**
     * Tests PrinterCli::checkStart
     */
    public function testCheckStart()
    {
        $check   = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, false);

        ob_start();
        $printer->checkStart($check);
        $printer->checkEnd($check);
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::checkStart
     */
    public function testCheckStartDebug()
    {
        $check   = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->checkStart($check);
        $printer->checkEnd($check);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'check') !== false);
    }

    /**
     * Tests PrinterCli::checkFailed
     */
    public function testCheckFailed()
    {
        $check   = array('type' => 'minsize');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->checkStart($check);
        $printer->checkFailed($check);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'failed') !== false);
    }

    /**
     * Tests PrinterCli::syncStart
     */
    public function testSyncStart()
    {
        $check   = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, false);

        ob_start();
        $printer->syncStart($check);
        $printer->syncEnd($check);
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::syncStart
     */
    public function testSyncStartDebug()
    {
        $sync    = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->syncStart($sync);
        $printer->syncEnd($sync);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'sync start') !== false);
    }

    /**
     * Tests PrinterCli::syncFailed
     */
    public function testSyncFailed()
    {
        $sync    = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->syncStart($sync);
        $printer->syncFailed($sync);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'failed') !== false);
    }

    /**
     * Tests PrinterCli::syncSkipped
     */
    public function testSyncSkipped()
    {
        $sync    = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->syncStart($sync);
        $printer->syncSkipped($sync);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'skipped') !== false);
    }

    /**
     * Tests PrinterCli::cleanupStart
     */
    public function testCleanupStart()
    {
        $cleanup = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, false);

        ob_start();
        $printer->cleanupStart($cleanup);
        $printer->cleanupEnd($cleanup);
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    /**
     * Tests PrinterCli::cleanupStart
     */
    public function testCleanupStartDebug()
    {
        $cleanup = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->cleanupStart($cleanup);
        $printer->cleanupEnd($cleanup);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'cleanup start') !== false);
    }

    /**
     * Tests PrinterCli::cleanupFailed
     */
    public function testCleanupFailed()
    {
        $cleanup = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->cleanupStart($cleanup);
        $printer->cleanupFailed($cleanup);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'failed') !== false);
    }

    /**
     * Tests PrinterCli::cleanupSkipped
     */
    public function testCleanupSkipped()
    {
        $cleanup = array('type' => 'TestType');
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->cleanupStart($cleanup);
        $printer->cleanupSkipped($cleanup);
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'skipped') !== false);
    }

    /**
     * Tests PrinterCli::debug
     */
    public function testDebug()
    {
        $printer = new PrinterCli(null, false, false, true);

        ob_start();
        $printer->debug('foo');
        $output = ob_get_clean();
        $this->assertTrue(strpos($output, 'foo') !== false);
    }

    /**
     * Tests PrinterCli::printResult
     */
    public function testPrintResultAllOk()
    {
        $printer = new PrinterCli(null, true, false, false);
        $result  = $this->getMockBuilder('\\phpbu\\App\\Result')
                        ->disableOriginalConstructor()
                        ->getMock();
        $backup  = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')
                        ->disableOriginalConstructor()
                        ->getMock();
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
        $printer = new PrinterCli(null, false, true, false);
        $result  = $this->getMockBuilder('\\phpbu\\App\\Result')
                        ->disableOriginalConstructor()
                        ->getMock();

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
        $printer = new PrinterCli(null, true, false, false);
        $result  = $this->getMockBuilder('\\phpbu\\App\\Result')
                        ->disableOriginalConstructor()
                        ->getMock();

        $backup  = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')
                        ->disableOriginalConstructor()
                        ->getMock();

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
        $printer = new PrinterCli(null, true, false, false);
        $result  = $this->getMockBuilder('\\phpbu\\App\\Result')
                        ->disableOriginalConstructor()
                        ->getMock();
        $e       = $this->getMockBuilder('\\phpbu\\App\\Exception')
                        ->disableOriginalConstructor()
                        ->getMock();
        $e->method('getMessage')->willReturn('foo');
        $e->method('getFile')->willReturn('foo.php');
        $e->method('getLine')->willReturn(1);
        $backup  = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')
                        ->disableOriginalConstructor()
                        ->getMock();

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
}
