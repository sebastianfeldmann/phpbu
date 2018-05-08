<?php
namespace phpbu\App\Backup\Sync;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use phpbu\App\Result;
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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class Ftp extends Xtp implements Simulator
{
    use Clearable;

    /**
     * FlySystem instance
     *
     * @var Filesystem
     */
    protected $flySystem;

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
        $this->flySystem = new Filesystem(new FtpAdapter([
            'host'     => $this->host,
            'username' => $this->user,
            'password' => $this->password,
            'root'     => $this->remotePath,
            'passive'  => $this->passive,
            'timeout'  => 30,
        ]), new Config([
            'disable_asserts' => true,
        ]));

        // silence ftp errors
        $old  = error_reporting(0);

        try {
            if ($this->flySystem->has($target->getFilename())) {
                $this->flySystem->update($target->getFilename(), file_get_contents($target->getPathname()));
            } else {
                $this->flySystem->write($target->getFilename(), file_get_contents($target->getPathname()));
            }
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        // run remote cleanup
        $this->cleanup($target, $result);

        error_reporting($old);
    }

    /**
     * Execute the remote clean up if needed
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function cleanup(Target $target, Result $result)
    {
        if (!$this->cleaner) {
            return;
        }

        $collector = new \phpbu\App\Backup\Collector\Ftp($target, $this->flySystem);
        $this->cleaner->cleanup($target, $collector, $result);
    }
}
