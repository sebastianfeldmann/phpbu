<?php
namespace phpbu\App\Log;

/**
 * Array utility test
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
        $appResult = $this->getAppResultMock(true);
        $appResult->expects($this->once())->method('wasSuccessful')->willReturn(true);
        $appResult->expects($this->once())->method('noneSkipped')->willReturn(true);
        $appResult->expects($this->once())->method('wasSuccessful')->willReturn(true);
        $appResult->method('getBackups')->willReturn(array());
        $appResult->expects($this->once())->method('getErrors')->willReturn(array());

        $mail = new Mail();
        $mail->setup(array('recipients' => 'test@example.com', 'transport' => 'null'));

        $mail->phpbuStart(array());
        $mail->debug('some test');
        $mail->backupStart(array());
        $mail->backupFailed(array());
        $mail->backupEnd(array());
        $mail->checkStart(array());
        $mail->checkFailed(array());
        $mail->checkEnd(array());
        $mail->syncStart(array());
        $mail->syncFailed(array());
        $mail->syncSkipped(array());
        $mail->syncEnd(array());
        $mail->cleanupStart(array());
        $mail->cleanupFailed(array());
        $mail->cleanupSkipped(array());
        $mail->cleanupEnd(array());
        $mail->phpbuEnd($appResult);
    }

    public function getAppResultMock()
    {
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        return $appResult;
    }
}
