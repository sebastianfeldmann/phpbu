<?php
namespace phpbu\App;

use phpbu\App\Log\NullLogger;
use PHPUnit\Framework\TestCase;

/**
 * Class Mockery
 *
 * @package phpbu\App
 */
class Mockery extends TestCase
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
        $compress = !empty($fileCompressed);
        $pathName = $compress ? $fileCompressed : $file;
        $target = $this->createMock(\phpbu\App\Backup\Target::class);
        $target->method('getPathnamePlain')->willReturn($file);
        $target->method('getPathname')->willReturn($pathName);
        $target->method('getPath')->willReturn(dirname($pathName));
        $target->method('fileExists')->willReturn(true);
        $target->method('shouldBeCompressed')->willReturn($compress);


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
}
