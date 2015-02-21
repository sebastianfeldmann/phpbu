<?php
namespace phpbu\Backup\Sync;

use Dropbox as DropboxApi;
use phpbu\App\Result;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;

/**
 * Dropbox
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.1
 */
class Dropbox implements Sync
{
    /**
     * API access token
     *
     * Goto https://www.dropbox.com/developers/apps
     * create your app
     *  - Dropbox api app
     *  - files and datastore
     *  - yes
     *  - provide some app name "my-dropbox-app"
     *  - generate access token to authenticate connection to your dropbox
     *
     * @var  string
     */
    protected $token;

    /**
     * Remote path
     *
     * @var string
     */
    protected $path;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Dropbox\\Client')) {
            throw new Exception('Dropbox sdk not loaded: use composer "dropbox/dropbox-sdk": "1.1.*" to install');
        }
        if (!isset($config['token']) || '' == $config['token']) {
            throw new Exception('API access token is mandatory');
        }
        if (!isset($config['path']) || '' == $config['path']) {
            throw new Exception('dropbox path is mandatory');
        }
        $this->token = $config['token'];
        $this->path  = $config['path'] . ( substr($config['path'], -1) !== '/' ? '/' : '' );
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\Backup\Sync::sync()
     * @param  \phpbu\backup\Target $target
     * @param  \phpbu\App\Result    $result
     * @throws \phpbu\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $sourcePath  = $target->getPathnameCompressed();
        $dropboxPath = $this->path . $target->getFilenameCompressed();
        $client      = new DropboxApi\Client($this->token, "phpbu/1.1.0");
        $pathError   = DropboxApi\Path::findErrorNonRoot($dropboxPath);

        if (substr(__FILE__, 0, 7) == 'phar://') {
            DropboxApi\RootCertificates::useExternalPaths();
        }

        if ($pathError !== null) {
            throw new Exception(sprintf('Invalid \'dropbox-path\': %s', $pathError));
        }

        $size = null;
        if (stream_is_local($sourcePath)) {
            $size = filesize($sourcePath);
        }

        try {
            $fp  = fopen($sourcePath, 'rb');
            $res = $client->uploadFile($dropboxPath, DropboxApi\WriteMode::add(), $fp, $size);
            fclose($fp);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }
        $result->debug('upload: done  (' . $res['size'] . ')');
    }
}
