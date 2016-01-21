<?php
namespace phpbu\App\Backup\Crypter;

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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.3.0
 */
class Mcrypt extends Key implements Crypter
{
    /**
     * Path to mcrypt command.
     *
     * @var string
     */
    private $pathToMcrypt;

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
                             ->deleteUncrypted(!$this->keepUncrypted);
        }

        return $this->executable;
    }
}
