<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * OpenSSL crypter class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.6
 */
class OpenSSL extends Abstraction implements Simulator
{
    /**
     * Path to mcrypt command.
     *
     * @var string
     */
    private $pathToOpenSSL;

    /**
     * Key file
     *
     * @var string
     */
    private $certFile;

    /**
     * Algorithm to use
     *
     * @var string
     */
    private $algorithm;

    /**
     * Password to use
     *
     * @var string
     */
    private $password;

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
    public function setup(array $options = [])
    {
        if (!Util\Arr::isSetAndNotEmptyString($options, 'algorithm')) {
            throw new Exception('openssl expects \'algorithm\'');
        }
        if (!Util\Arr::isSetAndNotEmptyString($options, 'password')
         && !Util\Arr::isSetAndNotEmptyString($options, 'certFile')) {
            throw new Exception('openssl expects \'key\' or \'password\'');
        }

        $this->pathToOpenSSL = Util\Arr::getValue($options, 'pathToOpenSSL', '');
        $this->keepUncrypted = Util\Str::toBoolean(Util\Arr::getValue($options, 'keepUncrypted', ''), false);
        $this->certFile      = $this->toAbsolutePath(Util\Arr::getValue($options, 'certFile', ''));
        $this->algorithm     = Util\Arr::getValue($options, 'algorithm', '');
        $this->password      = Util\Arr::getValue($options, 'password', '');
    }

    /**
     * Return file suffix of encrypted target.
     *
     * @see    \phpbu\App\Backup\Crypter
     * @return string
     */
    public function getSuffix() : string
    {
        return 'enc';
    }

    /**
     * Create the Executable to run the 'mcrypt' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\OpenSSL($this->pathToOpenSSL);
        $executable->encryptFile($target->getPathname());

        // use key or password to encrypt
        if (!empty($this->certFile)) {
            $executable->useSSLCert($this->certFile);
        } else {
            $executable->usePassword($this->password)
                       ->encodeBase64(true);
        }
        $executable->useAlgorithm($this->algorithm)
                   ->deleteUncrypted(!$this->keepUncrypted);
        return $executable;
    }
}
