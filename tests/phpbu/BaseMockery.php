<?php
namespace phpbu\App;

use phpbu\App\Log\NullLogger;

/**
 * BaseMockery trait
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 5.1.0
 */
trait BaseMockery
{
    /**
     * Create Target mock.
     *
     * @param  string $file
     * @param  string $fileCompressed
     * @return \phpbu\App\Backup\Target
     */
    protected function createTargetMock(string $file = '', string $fileCompressed = '')
    {
        if (strstr($fileCompressed, '.zip')) {
            $compressCmd    = 'zip';
            $compressSuffix = 'zip';
        } else {
            $compressCmd    = 'gzip';
            $compressSuffix = 'gz';
        }
        $compress = !empty($fileCompressed);
        $pathName = $compress ? $fileCompressed : $file;
        $path     = $this->createMock(\phpbu\App\Backup\Path::class);
        $path->method('getPath')->willreturn(dirname($pathName));
        $target   = $this->createMock(\phpbu\App\Backup\Target::class);
        $target->method('getFilename')->willReturn(basename($pathName));
        $target->method('getPathnamePlain')->willReturn($file);
        $target->method('getPathname')->willReturn($pathName);
        $target->method('getPath')->willReturn($path);
        $target->method('fileExists')->willReturn(true);
        $target->method('shouldBeCompressed')->willReturn($compress);
        $target->method('getCompression')->willReturn($this->createCompressionMock($compressCmd, $compressSuffix));

        return $target;
    }

    /**
     * Create Compression Mock.
     *
     * @param  string $cmd
     * @param  string $suffix
     * @return \phpbu\App\Backup\Target\Compression
     */
    protected function createCompressionMock($cmd, $suffix)
    {
        $compression = $this->createMock(\phpbu\App\Backup\Target\Compression::class);
        $compression->method('isPipeable')->willReturn(in_array($cmd, ['gzip', 'bzip2']));
        $compression->method('getCommand')->willReturn($cmd);
        $compression->method('getSuffix')->willReturn($suffix);
        $compression->method('getPath')->willReturn(PHPBU_TEST_BIN);

        return $compression;
    }

    /**
     * Create logger mock.
     *
     * @return \phpbu\App\Listener
     */
    protected function createNullLogger()
    {
        return new NullLogger();
    }

    /**
     * Create backup status mock.
     *
     * @param  string $dataPath
     * @param  bool   $isDir
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatusMock($dataPath = '', $isDir = false)
    {
        $handledCompression = empty($dataPath);
        $status = $this->createMock(\phpbu\App\Backup\Source\Status::class);
        $status->method('handledCompression')->willReturn($handledCompression);
        $status->method('getDataPath')->willReturn($dataPath);
        $status->method('isDirectory')->willReturn($isDir);

        return $status;
    }

    /**
     * Create source mock.
     *
     * @param $status
     * @return \phpbu\App\Backup\Source\Tar
     */
    protected function createSourceMock($status)
    {
        $source = $this->createMock(\phpbu\App\Backup\Source\Tar::class);
        $source->method('backup')->willReturn($status);
        $source->method('simulate')->willReturn($status);

        return $source;
    }

    /**
     * Create check mock.
     *
     * @param bool $pass
     * @return \phpbu\App\Backup\Check\SizeMin
     */
    protected function createCheckMock($pass = true)
    {
        $check = $this->createMock(\phpbu\App\Backup\Check\SizeMin::class);
        $check->method('pass')->willReturn($pass);
        $check->method('simulate')->willReturn(true);
        return $check;
    }

    /**
     * Create crypt mock
     *
     * @param  bool $success
     * @return \phpbu\App\Backup\Crypter\OpenSSL
     */
    protected function createCryptMock($success = true)
    {
        $crypter = $this->createMock(\phpbu\App\Backup\Crypter\OpenSSL::class);
        if (!$success) {
            $crypter->method('crypt')->will($this->throwException(new \phpbu\App\Backup\Crypter\Exception()));
        }
        return $crypter;
    }

    /**
     * Create sync mock
     *
     * @param  bool $success
     * @return \phpbu\App\Backup\Sync\Rsync
     */
    protected function createSyncMock($success = true)
    {
        $sync = $this->createMock(\phpbu\App\Backup\Sync\Rsync::class);
        if (!$success) {
            $sync->method('sync')->will($this->throwException(new \phpbu\App\Backup\Sync\Exception()));
        }
        return $sync;
    }

    /**
     * Create cleaner mock.
     *
     * @param bool $success
     * @return \phpbu\App\Backup\Cleaner\Outdated
     */
    protected function createCleanerMock($success = true)
    {
        $cleaner = $this->createMock(\phpbu\App\Backup\Cleaner\Outdated::class);
        if (!$success) {
            $cleaner->method('cleanup')->will($this->throwException(new \phpbu\App\Backup\Cleaner\Exception()));
        }
        return $cleaner;
    }
}
