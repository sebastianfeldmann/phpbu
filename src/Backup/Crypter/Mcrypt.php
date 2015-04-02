<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Cli\Binary;
use phpbu\App\Backup\Cli\Cmd;
use phpbu\App\Backup\Cli\Exec;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Mcrypt crypter class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.3.0
 */
class Mcrypt extends Binary implements Crypter
{
    /**
     * @var boolean
     */
    private $showStdErr;

    /**
     * Key to pass via cli
     *
     * @var string
     */
    private $key;

    /**
     * Key file
     *
     * @var string
     */
    private $keyFile;

    /**
     * Algorithm to use
     *
     * @var string
     */
    private $algorithm;

    /**
     * Hash to use
     *
     * @var string
     */
    private $hash;

    /**
     * Path to config file
     *
     * @var string
     */
    private $config;

    /**
     * Keep the not encrypted file
     *
     * @var boolean
     */
    private $keepUncrypted;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Crypter
     * @param  array $options
     * @throws Exception
     */
    public function setup(array $options = array())
    {
        $this->setupMcrypt($options);

        if (!Util\Arr::isSetAndNotEmptyString($options, 'algorithm')) {
            throw new Exception('mcrypt \'algorithm\' is mandatory');
        }

        $this->showStdErr    = Util\Str::toBoolean(Util\Arr::getValue($options, 'showStdErr', ''), false);
        $this->keepUncrypted = Util\Str::toBoolean(Util\Arr::getValue($options, 'keepUncrypted', ''), false);
        $this->key           = Util\Arr::getValue($options, 'key');
        $this->keyFile       = $this->toAbsolutePath(Util\Arr::getValue($options, 'keyFile'));
        $this->algorithm     = $options['algorithm'];
        $this->hash          = Util\Arr::getValue($options, 'hash');
        $this->config        = $this->toAbsolutePath(Util\Arr::getValue($options, 'config'));

        if (empty($this->key) && empty($this->keyFile)) {
            throw new Exception('one of \'key\' or \'keyFile\' is mandatory');
        }
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Crypter
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws Exception
     */
    public function crypt(Target $target, Result $result)
    {
        $exec   = $this->getExec($target);

        $mcrypt = $this->execute($exec);

        $result->debug('mcrypt:' . $mcrypt->getCmd());

        if (!$mcrypt->wasSuccessful()) {
            throw new Exception('mcrypt failed:' . PHP_EOL . $mcrypt->getOutputAsString());
        }
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Crypter
     * @return string
     */
    public function getSuffix()
    {
        return 'nc';
    }

    /**
     * Search for the mcrypt command.
     *
     * @param array $conf
     */
    protected function setupMcrypt(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = Util\Cli::detectCmdLocation('mcrypt', Util\Arr::getValue($conf, 'pathToMcrypt'));
        }
    }

    /**
     * Create the Exec to run the 'mcrypt' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Cli\Exec
     */
    public function getExec(Target $target)
    {
        if (null == $this->exec) {
            $this->exec = new Exec();
            $mcrypt     = new Cmd($this->binary);

            // no std error unless it is activated
            if (!$this->showStdErr) {
                $mcrypt->silence();
                // i kill you
            }

            $this->addOptionIfNotEmpty($mcrypt, '-u', !$this->keepUncrypted, false);
            $this->addOptionIfNotEmpty($mcrypt, '-k', $this->key, true, ' ');
            $this->addOptionIfNotEmpty($mcrypt, '-f', $this->keyFile, true, ' ');
            $this->addOptionIfNotEmpty($mcrypt, '-h', $this->hash, true, ' ');
            $this->addOptionIfNotEmpty($mcrypt, '-a', $this->algorithm, true, ' ');
            $this->addOptionIfNotEmpty($mcrypt, '-c', $this->config, true, ' ');

            $mcrypt->addArgument($target->getPathname());
            $this->exec->addCommand($mcrypt);
        }

        return $this->exec;
    }

    /**
     * Return an absolute path relative to the used configuration.
     *
     * @param  string $path
     * @param  string $default
     * @return string
     */
    protected function toAbsolutePath($path, $default = null)
    {
        return !empty($path) ? Util\Cli::toAbsolutePath($path, Util\Cli::getBase('configuration')) : $default;
    }
}
