<?php
namespace phpbu\Log;

use phpbu\App\Exception;
use phpbu\App\Listener;
use phpbu\App\Result;
use phpbu\Log\Logger;
use phpbu\Util\String;
use PHP_Timer;

/**
 * Mail Logger
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mail implements Listener, Logger
{
    /**
     * Mail subject
     *
     * @var string
     */
    protected $subject;

    /**
     * From email address
     *
     * @var string
     */
    protected $senderMail;

    /**
     * From name
     *
     * @var string
     */
    protected $senderName;

    /**
     * Transport type mail | smtp
     *
     * @var string
     */
    protected $transportType;

    /**
     * List of mail recepients
     *
     * @var array<string>
     */
    protected $recepients = array();

    /**
     * Amount of executed backups
     *
     * @var integer
     */
    private $numBackups = 0;

    /**
     * Amount of executed checks
     *
     * @var integer
     */
    private $numChecks = 0;

    /**
     * Amount of executed Syncs
     *
     * @var integer
     */
    private $numSyncs = 0;

    /**
     * Amount of executed Cleanups
     *
     * @var integer
     */
    private $numCleanups = 0;

    /**
     * Send mail only if there was an error
     *
     * @var boolean
     */
    private $sendOnlyOnError;

    /**
     * @see \phpbu\Log\Logger::setup
     */
    public function setup(array $options)
    {
        if (empty($options['recipients'])) {
            throw new Exception('no recipients given');
        }
        $mails                 = $options['recipients'];
        $server                = gethostname();
        $this->sendOnlyOnError = isset($options['sendOnlyOnError'])
                               ? String::toBoolean($options['sendOnlyOnError'], false)
                               : false;
        $this->subject         = isset($options['subejct'])
                               ? $options['subject']
                               : 'PHPBU backup report from ' . $server;
        $this->senderMail      = isset($options['sender.mail'])
                               ? $options['sender.mail']
                               : 'phpbu@' . $server;
        $this->senderName      = isset($options['sender.name'])
                               ? $options['sender.name']
                               : null;
        $this->transportType   = isset($options['transport'])
                               ? $options['transport']
                               : 'mail';
        $this->recepients      = array_map('trim', explode(';', $mails));

        // create transport an mailer
        $transport    = $this->createTransport($this->transportType, $options);
        $this->mailer = \Swift_Mailer::newInstance($transport);
    }

    /**
     * @see \phpbu\App\Listener::phpbuStart()
     */
    public function phpbuStart($settings)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::phpbuEnd()
     */
    public function phpbuEnd(Result $result)
    {
        $allGood = $result->wasSuccessful() && $result->noneSkipped() && $result->noneFailed();

        if (!$this->sendOnlyOnError || !$allGood) {
            $header  = $this->getHeaderHtml($result);
            $errors  = $this->getErrorHtml($result);
            $info    = $this->getInfoHtml($result);
            $footer  = $this->getFooterHtml();
            $body    = $header . $errors . $info . $footer;
            $sent    = false;

            try {
                $message = \Swift_Message::newInstance();
                $message->setSubject($this->subject)
                        ->setFrom($this->senderMail, $this->senderName)
                        ->setTo($this->recepients)
                        ->setBody($body)
                        ->addPart($body, 'text/html');

                $sent = $this->mailer->send($message);
            } catch ( \Exception $e ) {
                throw new Exception($e->getMessage());
            }
            if (!$sent) {
                throw new Exception('mail could not sent');
            }
        }
    }

    /**
     * @see \phpbu\App\Listener::backupStart()
     */
    public function backupStart($backup)
    {
        $this->numBackups++;
    }

    /**
     * @see \phpbu\App\Listener::backupEnd()
     */
    public function backupEnd($backup)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::backupFailed()
     */
    public function backupFailed($backup)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::checkStart()
     */
    public function checkStart($check)
    {
        $this->numChecks++;
    }

    /**
     * @see \phpbu\App\Listener::checkEnd()
     */
    public function checkEnd($check)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::checkFailed()
     */
    public function checkFailed($check)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::syncStart()
     */
    public function syncStart($sync)
    {
        $this->numSyncs++;
    }

    /**
     * @see \phpbu\App\Listener::syncEnd()
     */
    public function syncEnd($sync)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::syncSkipped()
     */
    public function syncSkipped($sync)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::syncFailed()
     */
    public function syncFailed($sync)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::cleanupStart()
     */
    public function cleanupStart($cleanup)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::cleanupEnd()
     */
    public function cleanupEnd($cleanup)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::cleanupSkipped()
     */
    public function cleanupSkipped($cleanup)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::cleanupFailed()
     */
    public function cleanupFailed($cleanup)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::debug()
     */
    public function debug($msg)
    {
        // do something fooish
    }

    /**
     *
     * @param  string $type
     * @param  array  $options
     * @throws \phpbu\App\Exception
     * @return \Swift_Transport
     */
    protected function createTransport($type, array $options)
    {
        switch ($type) {
            //
            // null transport, don't send andy mails
            //
            case 'null':
                 /* @var $transport \Swift_NullTransport */
                $transport = \Swift_NullTransport::newInstance();
                break;
            //
            // php mail config
            //
            case 'mail':
                /* @var $transport \Swift_MailTransport */
                $transport = \Swift_MailTransport::newInstance();
                break;
            //
            // smtp config
            //
            case 'smtp':
                if (!isset($options['smtp.host'])) {
                    throw new Exception('option \'smtp.host\' ist missing');
                }
                $host       = $options['smtp.host'];
                $port       = isset($options['smtp.port'])
                            ? $options['smtp.port']
                            : 25;
                $username   = isset($options['smtp.username'])
                            ? $options['smtp.username']
                            : null;
                $password   = isset($options['smtp.password'])
                            ? $options['smtp.password']
                            : null;
                $encryption = isset($options['smtp.encryption'])
                            ? $options['smtp.encryption']
                            : null;

                /* @var $transport \Swift_SmtpTransport */
                $transport = \Swift_SmtpTransport::newInstance($host, $port);

                if ($username && $password) {
                    $transport->setUsername($username)
                              ->setPassword($password);
                }
                if ($encryption) {
                    $transport->setEncryption($encryption);
                }
                break;
            //
            // sendmail configuration
            //
            case 'sendmail':
                if (!isset($options['sendmail.path'])) {
                    throw new Exception('option \'sendmail.path\' ist missing');
                }
                $path    = $options['sendmail.path'];
                $options = isset($options['sendmail.options']) ? ' ' . $options['sendmail.options'] : '';

                /* @var $transport \Swift_SendmailTransport */
                $transport = \Swift_SendmailTransport::newInstance($path . $options);
                break;
            //
            // UPS! no transport given
            //
            default:
                throw new Exception('mail transport not supported');
                break;
        }
        return $transport;
    }

    /**
     * Returns mail header html
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    protected function getHeaderHtml(Result $result)
    {
        $html = '';
        if (count($result->getBackups()) === 0) {
            $html = '<p style="color:orange;">No backups executed!</p>';
        } elseif ($result->wasSuccessful() && $result->noneSkipped() && $result->noneFailed()) {
            $html .= '<p>' .
                sprintf(
                    'OK (%d backup%s, %d check%s, %d sync%s, %d cleanup%s)',
                    count($result->getBackups()),
                    (count($result->getBackups()) == 1) ? '' : 's',
                    $this->numChecks,
                    ($this->numChecks == 1) ? '' : 's',
                    $this->numSyncs,
                    ($this->numSyncs == 1) ? '' : 's',
                    $this->numCleanups,
                    ($this->numCleanups == 1) ? '' : 's'
                ) .
                '</p>';
        } elseif ((!$result->noneSkipped() || !$result->noneFailed()) && $result->wasSuccessful()) {
            $html .= '<p style="color:orange;">' .
                sprintf(
                    'OK, but skipped or failed Syncs or Cleanups!<br />' .
                    'Backups: %d, Syncs: skipped|failed %d|%d, Cleanups: skipped|failed %d|%d.',
                    count($result->getBackups()),
                    $result->syncsSkippedCount(),
                    $result->syncsFailedCount(),
                    $result->cleanupsSkippedCount(),
                    $result->cleanupsFailedCount()
                ) .
                '</p>';
        } else {
            $html .= '<p style="color:red;">' .
                sprintf(
                    'FAILURE!<br />' .
                    'Backups: %d, failed Checks: %d, failed Syncs: %d, failed Cleanups: %d.',
                    count($result->getBackups()),
                    $result->checksFailedCount(),
                    $result->syncsFailedCount(),
                    $result->cleanupsFailedCount()
                ) .
                '</p>';
        }
        return $html;
    }

    /**
     * Get error informations
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    protected function getErrorHtml(Result $result)
    {
        $html = '';
        /* @var $e Exception */
        foreach ($result->getErrors() as $e) {
            $html .= '<p style="color:red;">' .
                sprintf(
                    "Exception '%s' with message '%s'<br />in %s:%d",
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ) .
                '</p>';

        }
        return $html;
    }

    /**
     * Returns backup informations html
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    protected function getInfoHtml(Result $result)
    {
        $html    = '';
        $backups = $result->getBackups();
        $amount  = count($backups);
        if ($amount > 0) {
            $i     = 0;
            $html .= '<table>';
            foreach ($backups as $backup) {
                $html .= '<tr><td colspan="4">';
                $html .= sprintf('backup %s ', $backup->getName());
                if ($backup->wasSuccessful() && $backup->noneSkipped() && $backup->noneFailed()) {
                    $html .= 'OK';
                } elseif ((!$backup->noneSkipped() || !$backup->noneFailed()) && $backup->wasSuccessful()) {
                    $html .= 'OK, but skipped or failed Syncs or Cleanups!';
                } else {
                    $html .= 'FAILED';
                }
                $html .= '</td></tr><tr><td></td><td>executed</td><td>skipped</td><td>failed</td></tr>';

                $html .= '<tr><td>checks</td>'
                       . '<td style="float:right;">' . $backup->checkCount() . '</td>'
                       . '<td></td>'
                       . '<td style="float:right;">' . $backup->checkCountFailed() . '</td></tr>'
                       . '<tr><td>syncs</td>'
                       . '<td style="float:right;">' . $backup->syncCount() . '</td>'
                       . '<td style="float:right;">' . $backup->syncCountSkipped() . '</td>'
                       . '<td style="float:right;">' . $backup->syncCountFailed() . '</td></tr>'
                       . '<tr><td>cleanups</td>'
                       . '<td style="float:right;">' . $backup->cleanupCount() . '</td>'
                       . '<td style="float:right;">' . $backup->cleanupCountSkipped() . '</td>'
                       . '<td style="float:right;">' . $backup->cleanupCountFailed() . '</td></tr>';

                // put spacing row between backups but not at the end of the table
                $i++;
                $html .= ( $i < $amount ? '<tr><td colspan="4">&nbsp;</td>' : '' );
            }
            $html .= '</table>';
        }

        return $html;
    }

    /**
     * Return mail body footer.
     *
     * @return string
     */
    protected function getFooterHtml()
    {
        return '<p>' . PHP_Timer::resourceUsage() . '</p>';
    }
}
