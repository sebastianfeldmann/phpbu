<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;
use phpbu\App\Util;
use phpseclib;

/**
 * Sftp sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Sftp extends Xtp
{
    /**
     * @var phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * Remote path where to put the backup
     *
     * @var Path
     */
    protected $remotePath;

    /**
     * Remote port of sftp server
     *
     * @var string
     */
    protected $port;

    /**
     * @var int
     */
    protected $time;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * SHA256 fingerprint of the server's host key, as printed by ssh-keygen -l
     *
     * @var string
     */
    protected $hostKeyFingerprint;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        // make sure either password or private key is configured
        if (!Util\Arr::isSetAndNotEmptyString($config, 'password')
         && !Util\Arr::isSetAndNotEmptyString($config, 'key')) {
            throw new Exception('\'password\' or \'key\' must be presented');
        }
        parent::setup($config);

        $this->time = time();
        $privateKey = Util\Arr::getValue($config, 'key', '');
        if (!empty($privateKey)) {
            // get absolute private key path
            $privateKey = Util\Path::toAbsolutePath($privateKey, Configuration::getWorkingDirectory());
            if (!file_exists($privateKey)) {
                throw new Exception("Private key not found at specified path");
            }
        }
        $this->privateKey = $privateKey;

        $fingerprint = Util\Arr::getValue($config, 'hostKeyFingerprint', '');
        if (!empty($fingerprint) && !preg_match('#^SHA256:[A-Za-z0-9+/]{43}$#D', $fingerprint)) {
            throw new Exception(
                '\'hostKeyFingerprint\' must be a SHA256 fingerprint like \'SHA256:...\', see ssh-keygen -l'
            );
        }
        $this->hostKeyFingerprint = $fingerprint;

        $this->remotePath = new Path($config['path'], $this->time);
        $this->port       = Util\Arr::getValue($config, 'port', '22');

        $this->setUpCleanable($config);
    }

    /**
     * Check for required loaded libraries or extensions.
     *
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function checkRequirements()
    {
        if (!class_exists('\\phpseclib\\Net\\SFTP')) {
            throw new Exception('phpseclib not installed - use composer to install "phpseclib/phpseclib" version 2.x');
        }
    }

    /**
     * Return implemented (*)TP protocol name.
     *
     * @return string
     */
    protected function getProtocolName()
    {
        return 'SFTP';
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
        $sftp           = $this->createClient();
        $remoteFilename = $target->getFilename();
        $localFile      = $target->getPathname();

        $this->validateRemotePath();

        foreach ($this->getRemoteDirectoryList() as $dir) {
            if (!$sftp->is_dir($dir)) {
                $result->debug(sprintf('creating remote dir \'%s\'', $dir));
                $sftp->mkdir($dir);
            }
            $result->debug(sprintf('change to remote dir \'%s\'', $dir));
            $sftp->chdir($dir);
        }

        $result->debug(sprintf('store file \'%s\' as \'%s\'', $localFile, $remoteFilename));
        $result->debug(sprintf('last error \'%s\'', $sftp->getLastSFTPError()));

        if (!$sftp->put($remoteFilename, $localFile, phpseclib\Net\SFTP::SOURCE_LOCAL_FILE)) {
            throw new Exception(sprintf('error uploading file: %s - %s', $localFile, $sftp->getLastSFTPError()));
        }

        // run remote cleanup
        $this->cleanup($target, $result);
    }

    /**
     * Create a sftp handle.
     *
     * @return \phpseclib\Net\SFTP
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function createClient() : phpseclib\Net\SFTP
    {
        if (!$this->sftp) {
            // silence phpseclib errors
            $old = error_reporting(0);
            try {
                $sftp = $this->createSftp();
                if (!empty($this->hostKeyFingerprint)) {
                    $this->verifyHostKey($sftp);
                }
                if (!$sftp->login($this->user, $this->getAuth())) {
                    throw new Exception(
                        sprintf(
                            'authentication failed for %s@%s%s',
                            $this->user,
                            $this->host,
                            empty($this->password) ? '' : ' with password ****'
                        )
                    );
                }
            } finally {
                // restore old error reporting
                error_reporting($old);
            }
            $this->sftp = $sftp;
        }

        return $this->sftp;
    }

    /**
     * Create the phpseclib client, not connected yet.
     *
     * @return \phpseclib\Net\SFTP
     */
    protected function createSftp() : phpseclib\Net\SFTP
    {
        return new phpseclib\Net\SFTP($this->host, $this->port);
    }

    /**
     * Compare the server's host key with the configured fingerprint before any credential is sent.
     *
     * getServerPublicHostKey() connects and checks that the server holds the private key of the
     * host key it presents; the fingerprint check then ties that key to the configured server.
     *
     * @param  \phpseclib\Net\SFTP $sftp
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function verifyHostKey(phpseclib\Net\SFTP $sftp)
    {
        $hostKey = $sftp->getServerPublicHostKey();
        if (!is_string($hostKey) || strpos($hostKey, ' ') === false) {
            throw new Exception(sprintf('could not read the host key of %s', $this->host));
        }
        $actual = self::fingerprint($hostKey);
        if (!hash_equals($this->hostKeyFingerprint, $actual)) {
            throw new Exception(
                sprintf(
                    'host key mismatch for %s: expected %s, got %s (%s key)',
                    $this->host,
                    $this->hostKeyFingerprint,
                    $actual,
                    self::keyType($hostKey)
                )
            );
        }
    }

    /**
     * Return the OpenSSH SHA256 fingerprint of a public key in "<type> <base64>" form.
     *
     * @param  string $publicKey
     * @return string
     */
    public static function fingerprint(string $publicKey) : string
    {
        $parts = explode(' ', trim($publicKey));
        $blob  = base64_decode($parts[1] ?? '', true);
        return 'SHA256:' . rtrim(base64_encode(hash('sha256', (string) $blob, true)), '=');
    }

    /**
     * Return the key type stored in a public key blob, e.g. "ssh-rsa".
     *
     * phpseclib prefixes the key with the signature algorithm (rsa-sha2-256), which
     * ssh-keyscan and ssh-keygen do not show, so the type is read from the blob itself.
     *
     * @param  string $publicKey
     * @return string
     */
    public static function keyType(string $publicKey) : string
    {
        $parts = explode(' ', trim($publicKey));
        $blob  = (string) base64_decode($parts[1] ?? '', true);
        if (strlen($blob) < 4) {
            return 'unknown';
        }
        $length = unpack('N', substr($blob, 0, 4))[1];
        return substr($blob, 4, $length);
    }

    /**
     * If a relative path is configured, determine absolute path and update local remote.
     *
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function validateRemotePath()
    {
        if (!Util\Path::isAbsolutePath($this->remotePath->getPath())) {
            $sftp             = $this->createClient();
            $this->remotePath = new Path($sftp->realpath('.') . '/' . $this->remotePath->getPathRaw(), $this->time);
        }
    }

    /**
     * Return a phpseclib authentication thingy.
     *
     * @return \phpseclib\Crypt\RSA|string
     */
    private function getAuth()
    {
        $auth = $this->password;
        // if private key should be used
        if ($this->privateKey) {
            $auth = new phpseclib\Crypt\RSA();
            $auth->loadKey(file_get_contents($this->privateKey));
            if ($this->password) {
                $auth->setPassword($this->password);
            }
        }
        return $auth;
    }

    /**
     * Return list of remote directories to travers.
     *
     * @return array
     */
    private function getRemoteDirectoryList() : array
    {
        return Util\Path::getDirectoryListFromAbsolutePath($this->remotePath->getPath());
    }

    /**
     * Creates collector for SFTP
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function createCollector(Target $target): Collector
    {
        return new Collector\Sftp($target, $this->remotePath, $this->createClient());
    }
}
