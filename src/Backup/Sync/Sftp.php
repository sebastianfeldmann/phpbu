<?php
namespace phpbu\App\Backup\Sync;

use phpseclib;
use phpbu\App\Result;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;

/**
 * Sftp sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Sftp implements Sync
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

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\phpseclib\\Net\\SFTP')) {
            throw new Exception('phpseclib not installed - use composer to install "phpseclib/phpseclib" version 2.x');
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
        error_reporting($old);

        $remoteFilename = $target->getFilename();
        $localFile      = $target->getPathname();

        if ('' !== $this->remotePath) {
            $remoteDirs = explode('/', $this->remotePath);
            foreach ($remoteDirs as $dir) {
                if (!$sftp->is_dir($dir)) {
                    $result->debug(sprintf('creating remote dir \'%s\'', $dir));
                    $sftp->mkdir($dir);
                }
                $result->debug(sprintf('change to remote dir \'%s\'', $dir));
                $sftp->chdir($dir);
            }
        }
        $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));
        $result->debug(sprintf('last error \'%s\'', $sftp->getLastSFTPError()));

        /** @noinspection PhpInternalEntityUsedInspection */
        if (!$sftp->put($remoteFilename, $localFile, phpseclib\Net\SFTP::SOURCE_LOCAL_FILE)) {
            throw new Exception(sprintf('error uploading file: %s - %s', $localFile, $sftp->getLastSFTPError()));
        }
    }
}
