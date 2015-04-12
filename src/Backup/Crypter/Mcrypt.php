<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
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
class Mcrypt extends Cli implements Crypter
{
    /**
     * Path to mcrypt command.
     *
     * @var string
     */
    private $pathToMcrypt;

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
        if (!Util\Arr::isSetAndNotEmptyString($options, 'algorithm')) {
            throw new Exception('mcrypt \'algorithm\' is mandatory');
        }

        $this->pathToMcrypt  = Util\Arr::getValue($options, 'pathToMcrypt');
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
        $mcrypt = $this->execute($target);

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
     * Create the Exec to run the 'mcrypt' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            $this->executable = new Executable\Mcrypt($this->pathToMcrypt);
            $this->executable->useAlgorithm($this->algorithm)
                             ->useKey($this->key)
                             ->useKeyFile($this->keyFile)
                             ->useConfig($this->config)
                             ->useHash($this->hash)
                             ->saveAt($target->getPathname())
                             ->deleteUncrypted(!$this->keepUncrypted)
                             ->showStdErr($this->showStdErr);
        }

        return $this->executable;
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
