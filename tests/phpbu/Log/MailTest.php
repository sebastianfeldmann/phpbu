<?php
namespace phpbu\App\Log;

/**
 * Mail Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Mail::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $events = Mail::getSubscribedEvents();

        $this->assertTrue(array_key_exists('phpbu.backup_start', $events));
        $this->assertTrue(array_key_exists('phpbu.check_start', $events));

        $this->assertEquals('onPhpbuEnd', $events['phpbu.app_end']);
    }

    /**
     * Test Mail::setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupNoRecipients()
    {
        $mail = new Mail();
        $mail->setup(array());
    }

    /**
     * Test Mail::setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupInvalidTransport()
    {
        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'foo'));
    }

    /**
     * Test Mail::setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupSmtpNoHost()
    {
        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'smtp'));
    }

    /**
     * Test Mail::setup
     */
    public function testSetupSmtpOk()
    {
        $mail = new Mail();
        $mail->setup(
            array(
                'recipients'      => 'test@example.com',
                'transport'       => 'smtp',
                'smtp.host'       => 'smtp.example.com',
                'smtp.username'   => 'user.name',
                'smtp.password'   => 'secret',
                'smtp.encryption' => 'ssl',
            )
        );
        $this->assertTrue(true, 'should work');
    }

    /**
     * Test Mail::setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupNoSendmailPath()
    {
        $mail = new Mail();
        $mail->setup(
            array(
                'recipients' => 'test@example.com',
                'transport'  => 'sendmail'
            )
        );
    }

    /**
     * Test Mail::setup
     */
    public function testSetupSendmailOk()
    {
        $mail = new Mail();
        $mail->setup(
            array(
                'recipients'    => 'test@example.com',
                'transport'     => 'sendmail',
                'sendmail.path' => '/bin/sendmail',
            )
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
        $appResult->method('getBackups')->willReturn(array());
        $appResult->expects($this->once())->method('getErrors')->willReturn(array());

        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'null'));

        $mail->onBackupStart($this->getEventMock('Backup\\Start', array()));
        $mail->onCheckStart($this->getEventMock('Check\\Start', array()));
        $mail->onSyncStart($this->getEventMock('Sync\\Start', array()));
        $mail->onCleanupStart($this->getEventMock('Cleanup\\Start', array()));
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
        $app->method('getBackups')->willReturn(array($backup));
        $app->expects($this->once())->method('getErrors')->willReturn(array());

        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'null'));

        $mail->onBackupStart($this->getEventMock('Backup\\Start', array()));
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
        $app->expects($this->once())->method('backupOkButSkipsOrFails')->willReturn(true);
        $app->method('getBackups')->willReturn(array($backup));
        $app->expects($this->once())->method('getErrors')->willReturn(array());

        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'null'));

        $mail->onBackupStart($this->getEventMock('Backup\\Start', array()));
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
        $app->expects($this->once())->method('backupOkButSkipsOrFails')->willReturn(false);
        $app->method('getBackups')->willReturn(array($backup));
        $app->expects($this->once())->method('getErrors')->willReturn(array($e));

        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'null'));

        $mail->onBackupStart($this->getEventMock('Backup\\Start', array()));
        $mail->onPhpbuEnd($this->getEventMock('App\\End', $app));
    }

    /**
     * Return App Result mock.
     *
     * @return \phpbu\App\Result
     */
    public function getAppResultMock()
    {
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        return $appResult;
    }

    /**
     * Return Backup Result mock.
     *
     * @return \phpbu\App\Result\Backup
     */
    public function getBackupResultMock()
    {
        $backup = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')
                       ->disableOriginalConstructor()
                       ->getMock();
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
        $e = $this->getMockBuilder('\\Exception')
                  ->disableOriginalConstructor()
                  ->getMock();

        $e->method('getMessage')->willReturn($msg);
        $e->method('getCode')->willReturn($code);
        return $e;
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
        $e = $this->getMockBuilder('\\phpbu\\App\\Event\\' . $type)
                  ->disableOriginalConstructor()
                  ->getMock();
        switch ($type) {
            case 'App\\End':
                $e->method('getResult')->willReturn($arg);
                break;
            case 'Debug':
                $e->method('getMessage')->willReturn($arg);
                break;
            default:
                $e->method('getConfig')->willReturn($arg);
                break;
        }
        return $e;
    }
}
