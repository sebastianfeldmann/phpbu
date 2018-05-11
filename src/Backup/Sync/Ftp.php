<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Collector;
use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Path;
use phpbu\App\Util\Str;

/**
 * Ftp sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Chris Hawes <me@chrishawes.net>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class Ftp extends Xtp implements Simulator
{
    use Clearable;

    /**
     * FTP connection stream
     *
     * @var resource
     */
    protected $ftpConnection;

    /**
     * Determine should ftp connects via passive mode.
     *
     * @var bool
     */
    protected $passive;

    /**
     * Setup the Ftp sync.
     *
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        $path = Arr::getValue($config, 'path', '');
        if ('/' === substr($path, 0, 1)) {
            throw new Exception('absolute path is not allowed');
        }
        parent::setup($config);

        $this->passive = Str::toBoolean(Arr::getValue($config, 'passive', ''), false);
        $this->setUpClearable($config);
    }

    /**
     * Check for required loaded libraries or extensions.
     *
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function checkRequirements()
    {
        if (!function_exists('ftp_connect')) {
            throw new Exception('ftp functions not enabled');
        }
    }

    /**
     * Return implemented (*)TP protocol name.
     *
     * @return string
     */
    protected function getProtocolName()
    {
        return 'FTP';
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
        $old = error_reporting(0);
        if (!$this->ftpConnection = ftp_connect($this->host)) {
            throw new Exception(
                sprintf(
                    'Unable to connect to ftp server %s',
                    $this->host
                )
            );
        }

        if (!ftp_login($this->ftpConnection, $this->user, $this->password)) {
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

        // Set passive mode if needed
        ftp_pasv($this->ftpConnection, $this->passive);

        $remoteFilename = $target->getFilename();
        $localFile      = $target->getPathname();

        if ('' !== $this->remotePath) {
            $remoteDirs = Path::getDirectoryListFromPath($this->remotePath);
            foreach ($remoteDirs as $dir) {
                if (!ftp_chdir($this->ftpConnection, $dir)) {
                    $result->debug(sprintf('creating remote dir \'%s\'', $dir));
                    ftp_mkdir($this->ftpConnection, $dir);
                    ftp_chdir($this->ftpConnection, $dir);
                } else {
                    $result->debug(sprintf('change to remote dir \'%s\'', $dir));
                }
            }
        }
        $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));

        if (!ftp_put($this->ftpConnection, $remoteFilename, $localFile, FTP_BINARY)) {
            $error   = error_get_last();
            $message = $error['message'];
            throw new Exception(sprintf('error uploading file: %s - %s', $localFile, $message));
        }

        error_reporting($old);

        $this->cleanup($target, $result);
    }

    /**
     * Creates collector for FTP
     *
     * @param \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target): Collector
    {
        return new \phpbu\App\Backup\Collector\Ftp($target, $this->ftpConnection);
    }
}
