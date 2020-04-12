<?php
namespace phpbu\App\Log;

use phpbu\App\Cli\Statistics;
use phpbu\App\Exception;
use phpbu\App\Event;
use phpbu\App\Listener;
use phpbu\App\Result;
use phpbu\App\Log\MailTemplate as TPL;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mail Logger
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mail implements Listener, Logger
{
    /**
     * Mailer instance
     *
     * @var \PHPMailer\PHPMailer\PHPMailer;
     */
    protected $mailer;

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
     * Transport type [mail|smtp|null]
     *
     * @var string
     */
    protected $transportType;

    /**
     * List of mail recipients
     *
     * @var array<string>
     */
    protected $recipients = [];

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
     * Amount of executed Crypts
     *
     * @var integer
     */
    private $numCrypts = 0;

    /**
     * Amount of executed Cleanups
     *
     * @var integer
     */
    private $numCleanups = 0;

    /**
     * Send mail only if there was an error
     *
     * @var bool
     */
    private $sendOnlyOnError = false;

    /**
     * Send mails on simulation runs
     *
     * @var bool
     */
    private $sendSimulating = true;

    /**
     * Is current execution a simulation
     *
     * @var bool
     */
    private $isSimulation = false;

    /**
     * Returns an array of event names this subscriber wants to listen to
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'phpbu.backup_start'  => 'onBackupStart',
            'phpbu.check_start'   => 'onCheckStart',
            'phpbu.crypt_start'   => 'onCryptStart',
            'phpbu.sync_start'    => 'onSyncStart',
            'phpbu.cleanup_start' => 'onCleanupStart',
            'phpbu.app_end'       => 'onPhpbuEnd',
        ];
    }

    /**
     * Setup the Logger
     *
     * @see    \phpbu\App\Log\Logger::setup
     * @param  array $options
     * @throws \phpbu\App\Exception
     */
    public function setup(array $options)
    {
        if (empty($options['recipients'])) {
            throw new Exception('no recipients given');
        }
        $mails                 = $options['recipients'];
        $server                = gethostname();
        $this->sendOnlyOnError = Str::toBoolean(Arr::getValue($options, 'sendOnlyOnError'), false);
        $this->sendSimulating  = Str::toBoolean(Arr::getValue($options, 'sendOnSimulation'), true);
        $this->subject         = Arr::getValue($options, 'subject', 'PHPBU backup report from ' . $server);
        $this->senderMail      = Arr::getValue($options, 'sender.mail', 'phpbu@' . $server);
        $this->senderName      = Arr::getValue($options, 'sender.name');
        $this->transportType   = Arr::getValue($options, 'transport', 'mail');
        $this->recipients      = array_map('trim', explode(';', $mails));
        $this->isSimulation    = Arr::getValue($options, '__simulate__', false);

        // create transport an mailer
        $this->mailer = new PHPMailer();
        $this->setupMailer($this->transportType, $options);
    }

    /**
     * Handle the phpbu end event
     *
     * @param  \phpbu\App\Event\App\End $event
     * @throws \phpbu\App\Exception
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function onPhpbuEnd(Event\App\End $event)
    {
        $result = $event->getResult();

        if ($this->shouldMailBeSend($result) === false) {
            return;
        }

        $header  = $this->getHeaderHtml();
        $status  = $this->getStatusHtml($result);
        $errors  = $this->getErrorHtml($result);
        $info    = $this->getInfoHtml($result);
        $footer  = $this->getFooterHtml();
        $body    = '<html><body ' . TPL::getSnippet('sBody') . '>'
                 . $header
                 . $status
                 . $errors
                 . $info
                 . $footer
                 . '</body></html>';
        $state   = $result->allOk() ? 'OK' : ($result->backupOkButSkipsOrFails() ? 'WARNING' : 'ERROR');

        $this->mailer->Subject = $this->subject . ' [' . ($this->isSimulation ? 'SIMULATION' : $state) . ']';
        $this->mailer->setFrom($this->senderMail, $this->senderName);
        $this->mailer->msgHTML($body);

        foreach ($this->recipients as $recipient) {
            $this->mailer->addAddress($recipient);
        }

        if ($this->transportType !== 'null') {
            $this->sendMail();
        }
    }

    /**
     * Backup start event.
     *
     * @param \phpbu\App\Event\Backup\Start $event
     */
    public function onBackupStart(Event\Backup\Start $event)
    {
        $this->numBackups++;
    }

    /**
     * Check start event.
     *
     * @param \phpbu\App\Event\Check\Start $event
     */
    public function onCheckStart(Event\Check\Start $event)
    {
        $this->numChecks++;
    }

    /**
     * Crypt start event.
     *
     * @param \phpbu\App\Event\Crypt\Start $event
     */
    public function onCryptStart(Event\Crypt\Start $event)
    {
        $this->numCrypts++;
    }

    /**
     * Sync start event.
     *
     * @param \phpbu\App\Event\Sync\Start $event
     */
    public function onSyncStart(Event\Sync\Start $event)
    {
        $this->numSyncs++;
    }

    /**
     * Cleanup start event.
     *
     * @param \phpbu\App\Event\Cleanup\Start $event
     */
    public function onCleanupStart(Event\Cleanup\Start $event)
    {
        $this->numCleanups++;
    }

    /**
     * Configure PHPMailer
     *
     * @param  string                         $type
     * @param  array                          $options
     * @throws \phpbu\App\Exception
     */
    protected function setupMailer($type, array $options)
    {
        switch ($type) {
            case 'null':
                $this->isSimulation = true;
                break;

            case 'smtp':
                $this->setupSmtpMailer($options);
                break;

            case 'mail':
            case 'sendmail':
                $this->setupSendmailMailer($options);
                break;

            // UPS! no transport given
            default:
                throw new Exception(sprintf('mail transport not supported: \'%s\'', $type));
        }
    }

    /**
     * Should a mail be send
     *
     * @param  \phpbu\App\Result $result
     * @return bool
     */
    protected function shouldMailBeSend(Result $result) : bool
    {
        // send mails if
        // there is an error or send error only is inactive
        // and
        // simulation settings do not prevent sending
        return (!$this->sendOnlyOnError || !$result->allOk()) && ($this->sendSimulating || !$this->isSimulation);
    }

    /**
     * Setup smtp mailing
     *
     * @param  array $options
     * @return void
     * @throws \phpbu\App\Exception
     */
    protected function setupSmtpMailer(array $options)
    {
        if (!isset($options['smtp.host'])) {
            throw new Exception('option \'smtp.host\' ist missing');
        }
        $host       = $options['smtp.host'];
        $port       = Arr::getValue($options, 'smtp.port', 25);
        $username   = Arr::getValue($options, 'smtp.username');
        $password   = Arr::getValue($options, 'smtp.password');
        $encryption = Arr::getValue($options, 'smtp.encryption');

        $this->mailer->isSMTP();
        $this->mailer->Host     = $host;
        $this->mailer->Port     = $port;

        if ($username && $password) {
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;
        }
        if ($encryption) {
            $this->mailer->SMTPSecure = $encryption;
        }
    }

    /**
     * Setup the php mail transport
     *
     * @param  array $options
     * @return void
     */
    protected function setupSendmailMailer(array $options)
    {
        // nothing to do here
    }

    /**
     * Send the email
     *
     * @throws \phpbu\App\Exception
     */
    protected function sendMail()
    {
        try {
            if (!$this->mailer->send()) {
                throw new Exception($this->mailer->ErrorInfo);
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Return mail header html
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function getHeaderHtml()
    {
        return '<table ' . TPL::getSnippet('sTableContent') . '><tr><td ' . TPL::getSnippet('sTableContentCol') . '>' .
               '<table ' . TPL::getSnippet('sTableHeader') . '><tr><td>PHPBU - backup report</td></tr></table>';
    }

    /**
     * Return mail status html
     *
     * @param  \phpbu\App\Result $result
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function getStatusHtml(Result $result)
    {
        if (count($result->getBackups()) === 0) {
            $color  = TPL::getSnippet('cStatusWARN');
            $status = 'WARNING';
        } elseif ($result->allOk()) {
            $color  = TPL::getSnippet('cStatusOK');
            $status = 'OK';
        } elseif ($result->backupOkButSkipsOrFails()) {
            $color  = TPL::getSnippet('cStatusWARN');
            $status = 'WARNING';
        } else {
            $color  = TPL::getSnippet('cStatusFAIL');
            $status = 'FAILURE';
        }
        $info = sprintf(
            '(%d %s, %d %s, %d %s, %d %s, %d %s)',
            count($result->getBackups()),
            Str::appendPluralS('backup', count($result->getBackups())),
            $this->numChecks,
            Str::appendPluralS('check', $this->numChecks),
            $this->numCrypts,
            Str::appendPluralS('crypt', $this->numCrypts),
            $this->numSyncs,
            Str::appendPluralS('sync', $this->numSyncs),
            $this->numCleanups,
            Str::appendPluralS('cleanup', $this->numCleanups)
        );
        $html = '<table ' . sprintf(TPL::getSnippet('sTableStatus'), $color) . '>' .
                 '<tr><td>' .
                  '<span ' . TPL::getSnippet('sTableStatusText') . '>' . date('Y-m-d H:i') . '</span>' .
                  '<h1 ' . TPL::getSnippet('sTableStatusHead') . '>' . $status . '</h1>' .
                  '<span ' . TPL::getSnippet('sTableStatusText') . '>' . $info . '</span>' .
                 '</td></tr>' .
                '</table>';

        return $html;
    }

    /**
     * Get error information
     *
     * @param  \phpbu\App\Result $result
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function getErrorHtml(Result $result)
    {
        $errors = $result->getErrors();

        if (count($errors) === 0) {
            return '';
        }

        $html  = '';
        $html .= '<table ' . TPL::getSnippet('sTableError') . '>';
        /* @var $e Exception */
        foreach ($errors as $e) {
            $html .= '<tr><td ' . TPL::getSnippet('sTableErrorCol') . '>' .
                sprintf(
                    "Exception '%s' with message '%s' in %s:%d",
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ) .
                '</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Return backup html information
     *
     * @param  \phpbu\App\Result $result
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function getInfoHtml(Result $result)
    {
        $backups = $result->getBackups();

        if (count($backups) === 0) {
            return '';
        }

        $html  = '';
        $html .= '<table ' . TPL::getSnippet('sTableBackup') . '>';
        /** @var \phpbu\App\Result\Backup $backup */
        foreach ($backups as $backup) {
            if ($backup->allOk()) {
                $color  = TPL::getSnippet('cStatusOK');
                $status = 'OK';
            } elseif ($backup->okButSkipsOrFails()) {
                $color  = TPL::getSnippet('cStatusWARN');
                $status = 'WARNING';
            } else {
                $color  = TPL::getSnippet('cStatusFAIL');
                $status = 'FAILURE';
            }
            $html .= '<tr>' .
                      '<td ' . sprintf(TPL::getSnippet('sTableBackupStatusColumn'), $color) . ' colspan="4">' .
                      sprintf('backup <em>%s</em>', $backup->getName()) .
                      ' <span ' . TPL::getSnippet('sTableBackupStatusText') . '>' . $status . '</span>' .
                      '</td>' .
                     '</tr>' .
                     '<tr>' .
                      '<td ' . TPL::getSnippet('sRowHead') . '>&nbsp;</td>' .
                      '<td ' . TPL::getSnippet('sRowHead') . ' align="right">executed</td>' .
                      '<td ' . TPL::getSnippet('sRowHead') . ' align="right">skipped</td>' .
                      '<td ' . TPL::getSnippet('sRowHead') . ' align="right">failed</td>' .
                     '</tr>';

            $html .= '<tr>' .
                      '<td ' . TPL::getSnippet('sRowCheck') . '>checks</td>' .
                      '<td ' . TPL::getSnippet('sRowCheck') . ' align="right">' .
                        $backup->checkCount() . '
                       </td>' .
                      '<td ' . TPL::getSnippet('sRowCheck') . ' align="right">
                        &nbsp;
                       </td>' .
                      '<td ' . TPL::getSnippet('sRowCheck') . ' align="right">' .
                        $backup->checkCountFailed() .
                      '</td>' .
                     '</tr>' .
                     '<tr>' .
                      '<td ' . TPL::getSnippet('sRowCrypt') . '>crypts</td>' .
                      '<td ' . TPL::getSnippet('sRowCrypt') . ' align="right">' .
                        $backup->cryptCount() .
                      '</td>' .
                      '<td ' . TPL::getSnippet('sRowCrypt') . ' align="right">' .
                        $backup->cryptCountSkipped() .
                      '</td>' .
                      '<td ' . TPL::getSnippet('sRowCrypt') . ' align="right">' .
                        $backup->cryptCountFailed() .
                      '</td>' .
                     '</tr>' .
                     '<tr>' .
                      '<td ' . TPL::getSnippet('sRowSync') . '>syncs</td>' .
                      '<td ' . TPL::getSnippet('sRowSync') . ' align="right">' .
                        $backup->syncCount() . '</td>' .
                      '<td ' . TPL::getSnippet('sRowSync') . ' align="right">' .
                        $backup->syncCountSkipped() .
                      '</td>' .
                      '<td ' . TPL::getSnippet('sRowSync') . ' align="right">' .
                        $backup->syncCountFailed() .
                      '</td>' .
                     '</tr>' .
                     '<tr>' .
                      '<td ' . TPL::getSnippet('sRowCleanup') . '>cleanups</td>' .
                      '<td ' . TPL::getSnippet('sRowCleanup') . ' align="right">' .
                        $backup->cleanupCount() .
                      '</td>' .
                      '<td ' . TPL::getSnippet('sRowCleanup') . ' align="right">' .
                        $backup->cleanupCountSkipped() .
                      '</td>' .
                      '<td ' . TPL::getSnippet('sRowCleanup') . ' align="right">' .
                        $backup->cleanupCountFailed() .
                      '</td>' .
                     '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Return mail body footer
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function getFooterHtml()
    {
        return '<p ' . TPL::getSnippet('sStats') . '>' . Statistics::resourceUsage() . '</p>' .
               '</td></tr></table>';
    }
}
