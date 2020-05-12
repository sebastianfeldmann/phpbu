<?php
namespace phpbu\App\Log;

use phpbu\App\Configuration\Backup;

/**
 * Json Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     MoeBrowne
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @since      Class available since Release 6.0.0
 */
class PrometheusTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Tests Prometheus::onBackupEnd
     */
    public function testOutput()
    {
        // result mock
        $result = $this->getResultMock();

        // config mock
        $backupConf = $this->createMock(Backup::class);
        $backupConf->method('getName')->willReturn('backupName');

        // backup start event mock
        $backupStartEvent = $this->createMock(\phpbu\App\Event\Backup\Start::class);
        $backupStartEvent->method('getConfiguration')->willReturn($backupConf);

        // backup end event mock
        $backupEndEvent = $this->createMock(\phpbu\App\Event\Backup\End::class);
        $backupEndEvent->method('getConfiguration')->willReturn($backupConf);

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $prometheus = new Prometheus();
        $prometheus->setup(['target' => 'php://output']);

        $prometheus->onBackupStart($backupStartEvent);
        $prometheus->onBackupEnd($backupEndEvent);

        ob_flush();
        ob_start();
        $prometheus->onPhpbuEnd($phpbuEndEvent);
        $output = ob_get_clean();

        $this->assertStringContainsString('phpbu_backup_success{name="foo"} 0', $output);
        $this->assertStringContainsString('phpbu_backup_duration{name="backupName"} ', $output);
        $this->assertStringContainsString('phpbu_backup_last_run{name="backupName"} ', $output);
        $this->assertStringContainsString('phpbu_backup_size{name="backupName"} ', $output);
    }

    /**
     * Create a app result mock
     *
     * @return \phpbu\App\Result
     */
    protected function getResultMock()
    {
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->method('started')->willReturn(microtime(true));
        $result->method('allOk')->willReturn(true);
        $result->method('getErrors')->willReturn([new \Exception('foo bar')]);
        $result->method('getBackups')->willReturn([$this->getBackupResultMock()]);
        $result->method('backupsFailedCount')->willReturn(0);
        $result->method('errorCount')->willReturn(1);

        return $result;
    }

    /**
     * Create a backup result mock
     *
     * @return \phpbu\App\Result\Backup
     */
    protected function getBackupResultMock()
    {
        $backup = $this->createMock(\phpbu\App\Result\Backup::class);
        $backup->method('getName')->willReturn('foo');
        $backup->method('wasSuccessful')->willReturn(true);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(0);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(0);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupCountSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        return $backup;
    }
}
