<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Result;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;

/**
 * Ftp sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Chris Hawes <me@chrishawes.net>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 */
class Ftp implements Sync
{
    /**
     * Host to connect to
     *
     * @var string
     */
    protected $host;

    /**
     * User to connect with
     *
     * @var string
     */
    protected $user;

    /**
     * Password to authenticate user
     *
     * @var string
     */
    protected $password;

    /**
     * Remote path where to put the backup
     *
     * @var string
     */
    protected $remotePath;

    public function setup(array $config)
    {
        if (!function_exists('ftp_connect')) {
            throw new Exception('ftp functions not enabled');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'host')) {
            throw new Exception('option \'host\' is missing');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'user')) {
            throw new Exception('option \'user\' is missing');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'password')) {
            throw new Exception('option \'password\' is missing');
        }
        $path = Arr::getValue($config, 'path', '');
        if ('/' === substr($path, 0, 1)) {
            throw new Exception('absolute path is not allowed');
        }
        $this->host       = $config['host'];
        $this->user       = $config['user'];
        $this->password   = $config['password'];
        $this->remotePath = Str::withoutTrailingSlash(Str::replaceDatePlaceholders($path));
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

        // silence ftp errors
        $old  = error_reporting(0);
        if (!$ftpConnection = ftp_connect($this->host)) {
            throw new Exception(
                sprintf(
                    'Unable to connect to ftp server %s',
                    $this->host
                )
            );
        }

        if (!ftp_login($ftpConnection, $this->user, $this->password)) {
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

        $remoteFilename = $target->getFilename();
        $localFile      = $target->getPathname();

        if ('' !== $this->remotePath) {
            $remoteDirs = explode('/', $this->remotePath);
            foreach ($remoteDirs as $dir) {
                if (!ftp_chdir($ftpConnection, $dir)) {
                    $result->debug(sprintf('creating remote dir \'%s\'', $dir));
                    ftp_mkdir($ftpConnection, $dir);
                    ftp_chdir($ftpConnection, $dir);
                } else {
                    $result->debug(sprintf('change to remote dir \'%s\'', $dir));
                }
            }
        }
        $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));
        $result->debug(sprintf('last error \'%s\'', error_get_last()));
        
        if (!ftp_put($ftpConnection, $remoteFilename, $localFile, FTP_BINARY)) {
            $error = error_get_last();
            $message = $error['message'];
            throw new Exception(sprintf('error uploading file: %s - %s', $localFile, $message));
        }

        error_reporting($old);

    }
}
