<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util;
use SebastianFeldmann\Ftp\Client;

/**
 * Ftp sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Chris Hawes <me@chrishawes.net>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class Ftp extends Xtp
{
    use Cleanable;

    /**
     * FTP connection stream
     *
     * @var \SebastianFeldmann\Ftp\Client
     */
    protected $ftpClient;

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
        $path = Util\Arr::getValue($config, 'path', '');
        if ('/' === substr($path, 0, 1)) {
            throw new Exception('absolute path is not allowed');
        }
        if (!Util\Arr::isSetAndNotEmptyString($config, 'password')) {
            throw new Exception('option \'password\' is missing');
        }
        parent::setup($config);

        $this->passive = Util\Str::toBoolean(Util\Arr::getValue($config, 'passive', ''), false);
        $this->setUpCleanable($config);
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
        try {
            $client         = $this->connect();
            $remoteFilename = $target->getFilename();
            $localFile      = $target->getPathname();

            $client->uploadFile($localFile, Util\Path::withTrailingSlash($this->remotePath), $remoteFilename);
            $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        $this->cleanup($target, $result);
    }

    /**
     * Return FTP client wrapping the ftp connection.
     *
     * @return \SebastianFeldmann\Ftp\Client
     */
    protected function connect()
    {
        if ($this->ftpClient === null) {
            $login           = $this->user . ($this->password ? ':' . $this->password : '');
            $this->ftpClient = new Client('ftp://' . $login . '@' . $this->host, $this->passive);
        }
        return $this->ftpClient;
    }

    /**
     * Creates collector for FTP
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target): Collector
    {
        return new Collector\Ftp($target, new Path($this->remotePath), $this->ftpClient);
    }
}
