<?php
namespace phpbu\App\Backup\Sync;

use Barracuda\Copy\API as CopycomApi;
use phpbu\App\Result;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;
use phpbu\App\Util\String;

/**
 * Copycom
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.2
 */
class Copycom implements Sync
{
    /**
     * API access key
     *
     * @var  string
     */
    protected $appKey;

    /**
     * API access token
     *
     * @var  string
     */
    protected $appSecret;

    /**
     * API access key
     *
     * @var  string
     */
    protected $userKey;

    /**
     * API access token
     *
     * @var  string
     */
    protected $userSecret;

    /**
     * Remote path
     *
     * @var string
     */
    protected $path;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Barracuda\\Copy\\API')) {
            throw new Exception('Copy api not loaded: use composer "barracuda/copy": "1.1.*" to install');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'app.key')) {
            throw new Exception('API access key is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'app.secret')) {
            throw new Exception('API access secret is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'user.key')) {
            throw new Exception('User access key is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'user.secret')) {
            throw new Exception('User access secret is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'path')) {
            throw new Exception('copy.com path is mandatory');
        }
        $this->appKey     = $config['app.key'];
        $this->appSecret  = $config['app.secret'];
        $this->userKey    = $config['user.key'];
        $this->userSecret = $config['user.secret'];
        $this->path       = String::withTrailingSlash(String::replaceDatePlaceholders($config['path']));
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
        $sourcePath = $target->getPathname();
        $targetPath = $this->path . $target->getFilename();

        $copy = new CopycomApi($this->appKey, $this->appSecret, $this->userKey, $this->userSecret);

        try {
            // open a file to upload
            $fh = fopen($sourcePath, 'rb');
            // upload the file in 1MB chunks
            $parts = array();
            while ($data = fread($fh, 1024 * 1024)) {
                $part = $copy->sendData($data);
                array_push($parts, $part);
            }
            fclose($fh);
            // finalize the file
            $copy->createFile($targetPath, $parts);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }
        $result->debug('upload: done');
    }
}
