<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Util;

/**
 * Gpg crypter class
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.1
 */
class Gpg extends Abstraction implements Simulator, Restorable
{
    /**
     * Path to gpg command
     *
     * @var string
     */
    private $pathToGpg;

    /**
     * Gpg user name
     *
     * @var string
     */
    private $user;

    /**
     * Keep the not encrypted file
     *
     * @var bool
     */
    private $keepUncrypted;

    /**
     * Setup
     *
     * @see    \phpbu\App\Backup\Crypter
     * @param  array $options
     * @throws Exception
     */
    public function setup(array $options = [])
    {
        if (!Util\Arr::isSetAndNotEmptyString($options, 'user')) {
            throw new Exception('gpg expects \'user\'');
        }

        $this->pathToGpg     = Util\Arr::getValue($options, 'pathToOpenSSL', '');
        $this->keepUncrypted = Util\Str::toBoolean(Util\Arr::getValue($options, 'keepUncrypted', ''), false);
        $this->user          = $this->toAbsolutePath(Util\Arr::getValue($options, 'user', ''));
    }

    /**
     * Return file suffix of encrypted target
     *
     * @see    \phpbu\App\Backup\Crypter
     * @return string
     */
    public function getSuffix() : string
    {
        return Executable\Gpg::SUFFIX;
    }

    /**
     * Decrypt the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     */
    public function restore(Target $target, Plan $plan)
    {
        $executable = $this->createDecryptionGpg($target);
        $plan->addDecryptionCommand($executable->getCommand());
    }

    /**
     * Create the Executable to run the 'gpg' command
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target): Executable
    {
        return $this->createEncryptionGpg($target);
    }

    /**
     * Create encryption Gpg
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    private function createEncryptionGpg(Target $target): Executable\Gpg
    {
        $executable = $this->createGpg($target);
        $executable->encryptFile($target->getPathname())
                   ->deleteSource(!$this->keepUncrypted);

        return $executable;
    }

    /**
     * Create decryption Gpg
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    private function createDecryptionGpg(Target $target): Executable\Gpg
    {
        $executable = $this->createGpg($target);
        $executable->decryptFile($target->getPathname())
                   ->deleteSource(false);

        return $executable;
    }

    /**
     * Setup an Gpg executable only thing missing is the decision of en or decryption
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    private function createGpg(Target $target): Executable\Gpg
    {
        $executable = new Executable\Gpg($this->pathToGpg);
        $executable->useUser($this->user);

        return $executable;
    }
}
