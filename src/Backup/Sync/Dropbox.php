<?php
namespace phpbu\Backup\Sync;

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
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.1
 */
class Dropbox implements Sync
{
    /**
     * API access token
     *
     * Goto https://www.dropbox.com/developers/apps
     * create your app
     *  - dropbox api app
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
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::setup()
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
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::sync()
     */
    public function sync(Target $target, Result $result)
    {
        $sourcePath  = $target->getPathnameCompressed();
        $dropboxPath = $this->path . $target->getFilenameCompressed();

        $client      = new \Dropbox\Client($this->token, "phpbu/1.1.0");

        $pathError = \Dropbox\Path::findErrorNonRoot($dropboxPath);
        if ($pathError !== null) {
            throw new Exception('Invalid <dropbox-path>: ' . $pathError);
        }

        $size = null;
        if (stream_is_local($sourcePath)) {
            $size = filesize($sourcePath);
        }

        try {
            $fp  = fopen($sourcePath, 'rb');
            $res = $client->uploadFile($dropboxPath, dbx\WriteMode::add(), $fp, $size);
            fclose($fp);
        } catch (\Exception $e) {
            fclose($fp);
            throw new Exception($e->getMessage(), null, $e);
        }
        $result->debug('upload: done  (' . $res['size'] . ')');
        /*
        $res
        [revision]     => somerevisionmumber
        [bytes]        => 12345
        [thumb_exists] =>
        [rev]          => somerevisionhash
        [modified]     => Day, DD Mon YYYY HH:MM:SS +0000
        [shareable]    =>
        [mime_type]    => application/octet-stream
        [path]         => uploaded file
        [is_dir]       =>
        [size]         => XX.X KB
        [root]         => dropbox
        [client_mtime] => Fri, 06 Feb 2015 21:44:33 +0000
        [icon]         => page_white_compressed
        */
    }
}
