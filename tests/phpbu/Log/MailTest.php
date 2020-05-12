<?php
namespace phpbu\App\Log;

use PHPUnit\Framework\TestCase;

/**
 * Mail Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MailTest extends TestCase
{
    /**
     * Tests Mail::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $events = Mail::getSubscribedEvents();

        $this->assertArrayHasKey('phpbu.backup_start', $events);
        $this->assertArrayHasKey('phpbu.check_start', $events);

        $this->assertEquals('onPhpbuEnd', $events['phpbu.app_end']);
    }

    /**
     * Test Mail::setup
     */
    public function testSetupNoRecipients()
    {
        $this->expectException('phpbu\App\Exception');
        $mail = new Mail();
        $mail->setup([]);
    }

    /**
     * Test Mail::setup
     */
    public function testSetupInvalidTransport()
    {
        $this->expectException('phpbu\App\Exception');
        $mail = new Mail();
        $mail->setup(['recipients' => 'test@example.com', 'transport' => 'foo']);
    }

    /**
     * Test Mail::setup
     */
    public function testSetupSmtpNoHost()
    {
        $this->expectException('phpbu\App\Exception');
        $mail = new Mail();
        $mail->setup(['recipients' => 'test@example.com', 'transport' => 'smtp']);
    }

    /**
     * Test Mail::setup
     */
    public function testSetupSmtpOk()
    {
        $mail = new Mail();
        $mail->setup(
            [
                'recipients'      => 'test@example.com',
                'transport'       => 'smtp',
                'smtp.host'       => 'smtp.example.com',
                'smtp.username'   => 'user.name',
                'smtp.password'   => 'secret',
                'smtp.encryption' => 'ssl',
            ]
        );
        $this->assertTrue(true, 'should work');
    }

    /**
     * Test Mail::setup
     */
    public function testSetupNoSendmailPath()
    {
        $mail = new Mail();
        $mail->setup(
            [
                'recipients' => 'test@example.com',
                'transport'  => 'sendmail'
            ]
        );
        $this->assertTrue(true, 'should work');
    }

    /**
     * Test Mail::setup
     */
    public function testSetupSendmailOk()
    {
        $mail = new Mail();
        $mail->setup(
            [
                'recipients'    => 'test@example.com',
                'transport'     => 'sendmail',
                'sendmail.path' => '/bin/sendmail',
            ]
        );
        $this->assertTrue(true, 'should work');
    }

    /**
     * Test Mail::setup
     */
    public function testSetupDefaultMailOk()
    {
        $mail = new Mail();
        $mail->setup(
            [
                'recipients' => 'test@example.com',
            ]
        );
        $this->assertTrue(true, 'should work');
    }

    /**
     * Tests Mail phpbu lifecycle.
     */
    public function testSequenceOk()
    {
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('allOk')->willReturn(true);
        $appResult->method('getBackups')->willReturn([]);
        $appResult->expects($this->once())->method('getErrors')->willReturn([]);

        $mail = new Mail();
        $mail->setup(['recipients' => 'test@example.com', 'transport' => 'null']);

        $mail->onBackupStart($this->getEventMock('Backup\\Start', []));
        $mail->onCheckStart($this->getEventMock('Check\\Start', []));
        $mail->onCryptStart($this->getEventMock('Crypt\\Start', []));
        $mail->onSyncStart($this->getEventMock('Sync\\Start', []));
        $mail->onCleanupStart($this->getEventMock('Cleanup\\Start', []));
        $mail->onPhpbuEnd($this->getEventMock('App\\End', $appResult));
    }

    /**
     * Tests Mail with a successful backup.
     */
    public function testBackupOk()
    {
        $backup = $this->getBackupResultMock();
        $backup->method('getName')->willReturn('backup');
        $backup->method('allOk')->willReturn(true);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(0);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(0);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupCountSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        $app = $this->getAppResultMock();
        $app->expects($this->exactly(2))->method('allOk')->willReturn(true);
        $app->method('getBackups')->willReturn([$backup]);
        $app->expects($this->once())->method('getErrors')->willReturn([]);

        $mail = new Mail();
        $mail->setup(['recipients' => 'test@example.com', 'transport' => 'null']);

        $mail->onBackupStart($this->getEventMock('Backup\\Start', []));
        $mail->onPhpbuEnd($this->getEventMock('App\\End', $app));
    }

    /**
     * Tests Mail with skipped or failed Syncs or Cleanups.
     */
    public function testBackupOkButSkipsOrFails()
    {
        $backup = $this->getBackupResultMock();
        $backup->method('getName')->willReturn('backup');
        $backup->method('allOk')->willReturn(false);
        $backup->method('okButSkipsOrFails')->willReturn(true);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(1);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(1);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupCountSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        $app = $this->getAppResultMock();
        $app->expects($this->exactly(2))->method('allOk')->willReturn(false);
        $app->expects($this->exactly(2))->method('backupOkButSkipsOrFails')->willReturn(true);
        $app->method('getBackups')->willReturn([$backup]);
        $app->expects($this->once())->method('getErrors')->willReturn([]);

        $mail = new Mail();
        $mail->setup(['recipients' => 'test@example.com', 'transport' => 'null']);

        $mail->onBackupStart($this->getEventMock('Backup\\Start', []));
        $mail->onPhpbuEnd($this->getEventMock('App\\End', $app));
    }

    /**
     * Tests Mail failed Backup.
     */
    public function testBackupFailed()
    {
        $backup = $this->getBackupResultMock();
        $backup->method('getName')->willReturn('backup');
        $backup->method('allOk')->willReturn(false);
        $backup->method('okButSkipsOrFails')->willReturn(false);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(1);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(1);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupCountSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        $e = $this->getExceptionMock('test', 0);

        $app = $this->getAppResultMock();
        $app->expects($this->exactly(2))->method('allOk')->willReturn(false);
        $app->expects($this->exactly(2))->method('backupOkButSkipsOrFails')->willReturn(false);
        $app->method('getBackups')->willReturn([$backup]);
        $app->expects($this->once())->method('getErrors')->willReturn([$e]);

        $mail = new Mail();
        $mail->setup(['recipients' => 'test@example.com', 'transport' => 'null']);

        $mail->onBackupStart($this->getEventMock('Backup\\Start', []));
        $mail->onPhpbuEnd($this->getEventMock('App\\End', $app));
    }

    /**
     * Return App Result mock.
     *
     * @return \phpbu\App\Result
     */
    public function getAppResultMock()
    {
        $appResult = $this->createMock(\phpbu\App\Result::class);
        return $appResult;
    }

    /**
     * Return Backup Result mock.
     *
     * @return \phpbu\App\Result\Backup
     */
    public function getBackupResultMock()
    {
        $backup = $this->createMock(\phpbu\App\Result\Backup::class);
        return $backup;
    }

    /**
     * Return Backup Result mock.
     *
     * @param  string $msg
     * @param  string $code
     * @return \phpbu\App\Result\Backup
     */
    public function getExceptionMock($msg, $code)
    {
        return new \Exception($msg, $code);
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
