<?php
namespace phpbu\Log;

use phpbu\App\Exception;
use phpbu\App\Listener;
use phpbu\App\Result;
use phpbu\Log\Logger;

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
     * @see \phpbu\Log\Logger::setup
     */
    public function setup(array $options)
    {
        if (empty($options['recipients'])) {
            throw new Exception('no recipients given');
        }
        $emails              = $options['recipients'];
        $server              = gethostname();
        $this->subject       = isset($options['subejct'])
                             ? $options['subject']
                             : 'PHPBU backup report from ' . $server;
        $this->senderMail    = isset($options['sender.mail'])
                             ? $options['sender.mail']
                             : 'phpbu@' . $server;
        $this->senderName    = isset($options['sender.name'])
                             ? $options['sender.name']
                             : null;
        $this->transportType = isset($options['transport'])
                             ? $options['transport']
                             : 'mail';
        $this->recepients    = array_map('trim', explode(';', $emails));

        // create transport an mailer
        $transport    = $this->createTransport($this->transportType, $options);
        $this->mailer = \Swift_Mailer::newInstance($transport);
    }

    /**
     * @see \phpbu\App\Listener::phpbuStart()
     */
    public function phpbuStart($settings)
    {

    }

    /**
     * @see \phpbu\App\Listener::phpbuEnd()
     */
    public function phpbuEnd(Result $result)
    {
        $body    = '';
        $message = \Swift_Message::newInstance();
        $message->setSubject($this->subject)
                ->setFrom($this->senderMail, $this->senderName)
                ->setTo($this->recepients)
                //->setBody('Here is the message itself')
                ->addPart($body, 'text/html');

        $sent = $this->mailer->send($message);
        if (!$sent) {
            throw new Exception('mail not send');
        }
    }

    /**
     * @see \phpbu\App\Listener::backupStart()
     */
    public function backupStart($backup)
    {
        // do something fooish
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
        // do something fooish
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
        // do something fooish
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
            case 'mail':
                $transport = \Swift_NullTransport::newInstance();
                //$transport = \Swift_MailTransport::newInstance();
                break;
            case 'smtp':
                if (!isset($options['smtp.host'])) {
                    throw new Exception('option \'smtp.host\' ist missing');
                }
                $host       = $options['smtp.host'];
                $port       = isset($options['smtp.port'])
                            ? $options['smtp.port']
                            : 25;
                $user       = isset($options['smtp.user'])
                            ? $options['smtp.user']
                            : null;
                $password   = isset($options['smtp.password'])
                            ? $options['smtp.password']
                            : null;
                $encryption = isset($options['smtp.encryption'])
                            ? $options['smtp.encryption']
                            : null;
                /* @var $transport \Swift_SmtpTransport */
                $transport  = \Swift_SmtpTransport::newInstance($host, $port);

                if ($user && $password) {
                    $transport->setUsername($options['smtp.username'])
                              ->setPassword($options['smtp.password']);
                }
                if ($encryption) {
                    $transport->setEncryption($encryption);
                }
                break;
            default:
                throw new Exception('mail transport not supported');
                break;
        }
        return $transport;
    }
}
