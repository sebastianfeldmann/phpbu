<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * OpenSSL crypter class
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.1.6
 */
class OpenSSL extends Abstraction implements Simulator, Restorable
{
    /**
     * Path to mcrypt command
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

    private $weakAlgorithms = [
        'rc2'          => true,
        'rc2-40'       => true,
        'rc2-64'       => true,
        'rc2-128'      => true,
        'rc2-40-cbc'   => true,
        'rc2-64-cbc'   => true,
        'rc2-cbc'      => true,
        'rc2-cfb'      => true,
        'rc2-ecb'      => true,
        'rc2-ofb'      => true,
        'rc4'          => true,
        'rc4-40'       => true,
        'des'          => true,
        'des-cbc'      => true,
        'des-cfb'      => true,
        'des-ecb'      => true,
        'des-ede'      => true,
        'des-ede-cbc'  => true,
        'des-ede-cfb'  => true,
        'des-ede-ofb'  => true,
        'des-ede3'     => true,
        'des-ede3-cbc' => true,
        'des-ede3-cfb' => true,
        'des-ede3-ofb' => true,
        'des-ofb'      => true,
        'des3'         => true,
        'desx'         => true,
        'seed'         => true,
        'seed-cbc'     => true,
        'seed-cfb'     => true,
        'seed-ecb'     => true,
        'seed-ofb'     => true,
    ];

    /**
     * @inheritDoc
     */
    public function crypt(Target $target, Result $result)
    {
        if ($this->isUsingWeakAlgorithm()) {
            $name = strtolower(get_class($this));

            $result->warn($name . ': The ' . $this->algorithm . ' algorithm is considered weak');
        }
        parent::crypt($target, $result);
    }


    /**
     * @inheritDoc
     */
    public function simulate(Target $target, Result $result)
    {
        if ($this->isUsingWeakAlgorithm()) {
            $name = strtolower(get_class($this));

            $result->warn($name . ': The ' . $this->algorithm . ' algorithm is considered weak');
        }
        parent::simulate($target, $result);
    }

    /**
     * Is the configured cipher secure enough
     *
     * @return bool
     * @throws \phpbu\App\Backup\Crypter\Exception
     */
    public function isUsingWeakAlgorithm(): bool
    {
        if (null === $this->algorithm) {
            throw new Exception('algorithm is not set');
        }

        return isset($this->weakAlgorithms[$this->algorithm]);
    }

    /**
     * Setup
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
            throw new Exception('openssl expects \'certFile\' or \'password\'');
        }

        $this->pathToOpenSSL = Util\Arr::getValue($options, 'pathToOpenSSL', '');
        $this->keepUncrypted = Util\Str::toBoolean(Util\Arr::getValue($options, 'keepUncrypted', ''), false);
        $this->certFile      = $this->toAbsolutePath(Util\Arr::getValue($options, 'certFile', ''));
        $this->algorithm     = Util\Arr::getValue($options, 'algorithm', '');
        $this->password      = Util\Arr::getValue($options, 'password', '');
    }

    /**
     * Return file suffix of encrypted target
     *
     * @see    \phpbu\App\Backup\Crypter
     * @return string
     */
    public function getSuffix() : string
    {
        return 'enc';
    }

    /**
     * Decrypt the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     * @throws \phpbu\App\Exception
     */
    public function restore(Target $target, Plan $plan)
    {
        $executable = $this->createDecryptionOpenSSL($target);
        $plan->addDecryptionCommand($executable->getCommand());
    }

    /**
     * Create the Executable to run the 'mcrypt' command
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    protected function createExecutable(Target $target) : Executable
    {
        return $this->createEncryptionOpenSSL($target);
    }

    /**
     * Create encryption OpenSSL
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable\OpenSSL
     * @throws \phpbu\App\Exception
     */
    private function createEncryptionOpenSSL(Target $target): Executable\OpenSSL
    {
        $executable = $this->createOpenSSL($target);
        $executable->encryptFile($target->getPathname())
                   ->deleteSource(!$this->keepUncrypted);

        return $executable;
    }

    /**
     * Create decryption OpenSSL
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable\OpenSSL
     * @throws \phpbu\App\Exception
     */
    private function createDecryptionOpenSSL(Target $target): Executable\OpenSSL
    {
        $executable = $this->createOpenSSL($target);
        $executable->decryptFile($target->getPathname())
                   ->deleteSource(false);

        return $executable;
    }

    /**
     * Setup an OpenSSL executable only thing missing is the decision of en or decryption
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable\OpenSSL
     * @throws \phpbu\App\Exception
     */
    private function createOpenSSL(Target $target): Executable\OpenSSL
    {
        $executable = new Executable\OpenSSL($this->pathToOpenSSL);
        // use key or password to encrypt
        if (!empty($this->certFile)) {
            $executable->useSSLCert($this->certFile);
        } else {
            $executable->usePassword($this->password)
                       ->encodeBase64(true);
        }
        $executable->useAlgorithm($this->algorithm);

        return $executable;
    }
}
