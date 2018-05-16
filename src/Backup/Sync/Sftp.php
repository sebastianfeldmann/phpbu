<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;
use phpbu\App\Util;
use phpseclib;

/**
 * Sftp sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Sftp extends Xtp
{
    /**
     * @var phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * Remote path where to put the backup
     *
     * @var Path
     */
    protected $remotePath;

    /**
     * @var int
     */
    protected $time;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        if (!Util\Arr::isSetAndNotEmptyString($config, 'password') && !Util\Arr::isSetAndNotEmptyString($config, 'private_key')) {
            throw new Exception('\'password\' or \'private_key\' must be presented');
        }
        parent::setup($config);

        $this->time = time();
        $privateKey = Util\Arr::getValue($config, 'key', '');
        if (!empty($privateKey)) {
            // get absolute private key path
            $privateKey = Util\Path::toAbsolutePath($privateKey, Configuration::getWorkingDirectory());
            if (!file_exists($privateKey)) {
                throw new Exception("Private key not found at specified path");
            }
        }
        $this->privateKey = $privateKey;
        $this->remotePath = new Path($config['path'], $this->time);

        $this->setUpClearable($config);
    }

    /**
     * Check for required loaded libraries or extensions.
     *
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function checkRequirements()
    {
        if (!class_exists('\\phpseclib\\Net\\SFTP')) {
            throw new Exception('phpseclib not installed - use composer to install "phpseclib/phpseclib" version 2.x');
        }
    }

    /**
     * Return implemented (*)TP protocol name.
     *
     * @return string
     */
    protected function getProtocolName()
    {
        return 'SFTP';
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::sync()
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $this->sftp     = $this->login();
        $remoteFilename = $target->getFilename();
        $localFile      = $target->getPathname();

        foreach ($this->getRemoteDirectoryList() as $dir) {
            if (!$this->sftp->is_dir($dir)) {
                $result->debug(sprintf('creating remote dir \'%s\'', $dir));
                $this->sftp->mkdir($dir);
            }
            $result->debug(sprintf('change to remote dir \'%s\'', $dir));
            $this->sftp->chdir($dir);
        }

        $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));
        $result->debug(sprintf('last error \'%s\'', $this->sftp->getLastSFTPError()));

        if (!$this->sftp->put($remoteFilename, $localFile, phpseclib\Net\SFTP::SOURCE_LOCAL_FILE)) {
            throw new Exception(sprintf('error uploading file: %s - %s', $localFile, $this->sftp->getLastSFTPError()));
        }

        // run remote cleanup
        $this->cleanup($target, $result);
    }

    /**
     * Create a sftp handle.
     *
     * @return \phpseclib\Net\SFTP
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function login() : phpseclib\Net\SFTP
    {
        // silence phpseclib
        $old  = error_reporting(0);
        $sftp = new phpseclib\Net\SFTP($this->host);
        $auth = $this->getAuth();

        if (!$sftp->login($this->user, $auth)) {
            error_reporting($old);
            throw new Exception(
                sprintf(
                    'authentication failed for %s@%s%s',
                    $this->user,
                    $this->host,
                    empty($this->password) ? '' : ' with password ****'
                )
            );
        }
        // restore old error reporting
        error_reporting($old);

        // if presented relative path, determine absolute path and update
        if (!Util\Path::isAbsolutePath($this->remotePath->getPath())) {
            $this->remotePath = new Path($sftp->realpath('.') . '/' . $this->remotePath->getPathRaw(), $this->time);
        }

        return $sftp;
    }

    /**
     * Return a phpseclib authentication thingy.
     *
     * @return \phpseclib\Crypt\RSA|string
     */
    private function getAuth()
    {
        $auth = $this->password;
        // if private key should be used
        if ($this->privateKey) {
            $auth = new phpseclib\Crypt\RSA();
            $auth->loadKey(file_get_contents($this->privateKey));
            if ($this->password) {
                $auth->setPassword($this->password);
            }
        }
        return $auth;
    }

    /**
     * Return list of remote directories to travers.
     *
     * @return array
     */
    private function getRemoteDirectoryList() : array
    {
        return Util\Path::getDirectoryListFromAbsolutePath($this->remotePath->getPath());
    }

    /**
     * Creates collector for SFTP
     *
     * @param \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target): Collector
    {
        return new Collector\Sftp($target, $this->sftp, $this->remotePath);
    }
}
