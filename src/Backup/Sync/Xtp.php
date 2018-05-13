<?php
namespace phpbu\App\Backup\Sync;

use phpseclib;
use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

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
abstract class Xtp implements Simulator
{
    use Clearable;

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
     * Check for loaded libraries or extensions.
     *
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    abstract protected function checkRequirements();

    /**
     * Return implemented (*)TP protocol name.
     *
     * @return string
     */
    abstract protected function getProtocolName();

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        $this->checkRequirements();
        if (!Util\Arr::isSetAndNotEmptyString($config, 'host')) {
            throw new Exception('option \'host\' is missing');
        }
        if (!Util\Arr::isSetAndNotEmptyString($config, 'user')) {
            throw new Exception('option \'user\' is missing');
        }
        if (!Util\Arr::isSetAndNotEmptyString($config, 'password')) {
            throw new Exception('option \'password\' is missing');
        }
        $path = Util\Arr::getValue($config, 'path', '');
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->remotePath = Util\Path::withoutTrailingSlash(Util\Path::replaceDatePlaceholders($path));
    }

    /**
     * Simulate the sync execution
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug(
            'sync backup to ' . $this->getProtocolName() . ' server' . PHP_EOL
            . '  host:     ' . $this->host . PHP_EOL
            . '  user:     ' . $this->user . PHP_EOL
            . '  password:  ********' . PHP_EOL
            . '  path:     ' . $this->remotePath . PHP_EOL
        );

        $this->simulateRemoteCleanup($target, $result);
    }
}
