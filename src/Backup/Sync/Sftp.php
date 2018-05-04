<?php
namespace phpbu\App\Backup\Sync;

use phpseclib;
use phpbu\App\Result;
use phpbu\App\Backup\Target;

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
class Sftp extends Xtp implements Simulator
{
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
        $sftp           = $this->login();
        $remoteFilename = $target->getFilename();
        $localFile      = $target->getPathname();

        foreach ($this->getRemoteDirectoryList() as $dir) {
            if (!$sftp->is_dir($dir)) {
                $result->debug(sprintf('creating remote dir \'%s\'', $dir));
                $sftp->mkdir($dir);
            }
            $result->debug(sprintf('change to remote dir \'%s\'', $dir));
            $sftp->chdir($dir);
        }

        $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));
        $result->debug(sprintf('last error \'%s\'', $sftp->getLastSFTPError()));

        if (!$sftp->put($remoteFilename, $localFile, phpseclib\Net\SFTP::SOURCE_LOCAL_FILE)) {
            throw new Exception(sprintf('error uploading file: %s - %s', $localFile, $sftp->getLastSFTPError()));
        }
    }

    /**
     * Create a sftp handle.
     *
     * @return \phpseclib\Net\SFTP
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    private function login() : phpseclib\Net\SFTP
    {
        // silence phpseclib
        $old  = error_reporting(0);
        $sftp = new phpseclib\Net\SFTP($this->host);
        if (!$sftp->login($this->user, $this->password)) {
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

        return $sftp;
    }

    /**
     * Return list of remote directories to travers.
     *
     * @return array
     */
    public function getRemoteDirectoryList() : array
    {
        $remoteDirs = [];
        if ('' !== $this->remotePath) {
            $remoteDirs = explode('/', $this->remotePath);
            // if path is absolute, fix first part of array that was empty string
            if (substr($this->remotePath, 0, 1) === '/') {
                if (isset($remoteDirs[0])) {
                    $remoteDirs[0] = '/';
                }
            }
        }
        return array_filter($remoteDirs);
    }
}
