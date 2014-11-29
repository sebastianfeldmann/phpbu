<?php
namespace phpbu\Backup\Sync;

use NET_SFTP;
use phpbu\App\Result;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;

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

    public function setup(array $config)
    {
        if (!class_exists('\\Net_SFTP')) {
            throw new Exception('phpseclib not installed - use composer to install "phpseclib/phpseclib"');
        }
        if (empty($config['host'])) {
            throw new Exception('option \'host\' is missing');
        }
        if (!isset($config['user'])) {
            throw new Exception('option \'user\' is missing');
        }
        if (!isset($config['password'])) {
            throw new Exception('option \'password\' is missing');
        }
        $this->host       = $config['host'];
        $this->user       = $config['user'];
        $this->password   = $config['password'];
        $this->remotePath = !empty($config['path']) ? $config['path'] : '';
    }

    public function sync(Target $target, Result $result)
    {
        $sftp = new Net_SFTP($this->host);
        if (!$sftp->login($this->user, $this->password)) {
            throw new Exception(
                sprintf(
                    'authentication failed for %s@%s%s',
                    $this->user,
                    $this->host,
                    empty($this->password) ? '' : ' using password'
                )
            );
        }

        $remoteFilename = $target->getFilenameCompressed();
        $localFile      = $target->getPathname(true);

        if ('' !== $this->remotePath) {
            $remoteDirs = explode('/', $this->remotePath);
            foreach ($remoteDirs as $dir) {
                if (!$sftp->is_dir($dir)) {
                    $sftp->mkdir($dir);
                }
                $sftp->chdir($dir);
            }
        }
        if (!$sftp->put($remoteFilename, $localFile, NET_SFTP_LOCAL_FILE)) {
            throw new Exception(sprintf('error uploading file: %s', $localFile));
        }
    }
}
