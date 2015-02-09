<?php
namespace phpbu\Backup\Sync;

use Dropbox as dbx;
use phpbu\App\Result;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;

/**
 * Copycom
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.1
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
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::setup()
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Barracuda\\Copy\\API')) {
            throw new Exception('Copy api not loaded: use composer "barracuda/copy": "1.1.*" to install');
        }
        if (!isset($config['app.key']) || '' == $config['app.key']) {
            throw new Exception('API access key is mandatory');
        }
        if (!isset($config['app.secret']) || '' == $config['app.secret']) {
            throw new Exception('API access secret is mandatory');
        }
        if (!isset($config['user.key']) || '' == $config['user.key']) {
            throw new Exception('User access key is mandatory');
        }
        if (!isset($config['user.secret']) || '' == $config['user.secret']) {
            throw new Exception('User access secret is mandatory');
        }
        if (!isset($config['path']) || '' == $config['path']) {
            throw new Exception('dropbox path is mandatory');
        }
        $this->appKey     = $config['app.key'];
        $this->appSecret  = $config['app.secret'];
        $this->userKey    = $config['user.key'];
        $this->userSecret = $config['user.secret'];
        $this->path  = $config['path'] . ( substr($config['path'], -1) !== '/' ? '/' : '' );
    }

    /**
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::sync()
     */
    public function sync(Target $target, Result $result)
    {
        $sourcePath = $target->getPathnameCompressed();
        $targetPath = $this->path . $target->getFilenameCompressed();

        $copy = new \Barracuda\Copy\API($this->appKey, $this->appSecret, $this->userKey, $this->userSecret);

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
