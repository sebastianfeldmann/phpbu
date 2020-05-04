<?php
namespace phpbu\App\Util;

use PHPUnit\Framework\TestCase;

/**
 * Cli utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CliTest extends TestCase
{
    /**
     * Fake global state
     *
     * @var array
     */
    private static $server;

    /**
     * Backup $_SERVER settings.
     */
    public function setup() : void
    {
        self::$server = $_SERVER;
    }

    /**
     * Restore $_SERVER settings.
     */
    protected function tearDown() : void
    {
        $_SERVER = self::$server;
    }

    /**
     * Test detectCmdLocation Exception
     */
    public function testDetectCmdFail()
    {
        $this->expectException('RuntimeException');
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            // can't be tested on windows system
            $this->assertTrue(true);
        } else {
            // assume ls should be there
            $cmd = Cli::detectCmdLocation('someStupidCommand');
            $this->assertFalse(true, $cmd . ' should not be found');
        }
    }

    /**
     * Test detectCmdLocation Exception with path.
     */
    public function testDetectCmdFailWithPath()
    {
        $this->expectException('RuntimeException');
        // assume ls should be there
        $cmd = Cli::detectCmdLocation('someStupidCommand', '/tmp');
        $this->assertFalse(true, $cmd . ' should not be found');
    }

    /**
     * Test detectCmdLocation
     */
    public function testDetectCmdLocationWhich()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            // can't be tested on windows system
            $this->assertTrue(true);
        } else {
            // assume ls should be there
            $ls = Cli::detectCmdLocation('ls');
            $this->assertTrue(!empty($ls), 'ls command should be found');
        }
    }

    /**
     * Test detectCmdLocation
     */
    public function testDetectCmdLocationWithProvidedPath()
    {
        $cmd     = 'foo';
        $cmdPath = $this->createTempCommand($cmd);
        $result  = Cli::detectCmdLocation($cmd, dirname($cmdPath));
        // cleanup tmp executable
        $this->removeTempCommand($cmdPath);

        $this->assertEquals($cmdPath, $result, 'foo command should be found');
    }

    /**
     * Test detectCmdLocation
     */
    public function testDetectCmdLocationWithOptionalLocation()
    {
        $cmd     = 'bar';
        $cmdPath = $this->createTempCommand($cmd);
        $result  = Cli::detectCmdLocation($cmd, '', [dirname($cmdPath)]);
        // cleanup tmp executable
        $this->removeTempCommand($cmdPath);

        $this->assertEquals($cmdPath, $result, 'foo command should be found');
    }

    /**
     * Tests Cli::getEnvPath
     */
    public function testGetEnvPathFail()
    {
        $this->expectException('RuntimeException');
        unset($_SERVER['PATH']);
        unset($_SERVER['Path']);
        unset($_SERVER['path']);
        $path = Cli::getEnvPath();
    }

    /**
     * Tests Cli::formatWithColor
     */
    public function testFormatWithColor()
    {
        $plainText   = 'Mein Test';
        $coloredText = Cli::formatWithColor('fg-black, bg-green', $plainText);

        $this->assertStringContainsString("\x1b[0m", $coloredText);
    }

    /**
     * Tests Cli::formatWithColor
     */
    public function testFormatWithColorEmptyLine()
    {
        $plainText   = '';
        $coloredText = Cli::formatWithColor('fg-black, bg-green', $plainText);

        $this->assertStringNotContainsString("\x1b[0m", $coloredText);
    }

    /**
     * Tests Cli::formatWithAsterisk
     */
    public function testFormatWithAsterisk()
    {
        $plainText     = 'Mein Test ';
        $decoratedText = Cli::formatWithAsterisk($plainText);

        $this->assertEquals(75, strlen(trim($decoratedText)));
        $this->assertStringContainsString('*', $decoratedText);
    }

    /**
     * Tests Cli::canPipe
     */
    public function testCanPipe()
    {
        $this->assertEquals(!defined('PHP_WINDOWS_VERSION_BUILD'), Cli::canPipe());
    }

    /**
     * Tests Cli::removeDir
     */
    public function testRemoveDir()
    {
        $dir         = sys_get_temp_dir();
        $dirToDelete = $dir . '/delete-dir';
        $subDir      = $dirToDelete . '/delete-sub-dir';

        $file        = $dirToDelete . '/fiz.txt';
        $fileInSub   = $subDir . '/baz.txt';

        mkdir($subDir, 0700, true);
        file_put_contents($file, 'fiz');
        file_put_contents($fileInSub, 'baz');

        Cli::removeDir($dirToDelete);

        $this->assertFileNotExists($file);
        $this->assertFileNotExists($fileInSub);
        $this->assertFileNotExists($subDir);
        $this->assertFileNotExists($dirToDelete);
    }

    /**
     * Tests Cli::getCommandLocations
     */
    public function testCommandLocationsDefault()
    {
        $list = Cli::getCommandLocations('tar');
        $this->assertCount(0, $list);

        $list = Cli::getCommandLocations('mysqldump');
        $this->assertCount(2, $list);
    }

    /**
     * Tests Cli::getCommandLocations
     */
    public function testAddCommandLocations()
    {
        Cli::addCommandLocation('mongodump', '/foo/mongodump');
        $list = Cli::getCommandLocations('mongodump');

        $this->assertCount(1, $list);
        $this->assertEquals('/foo/mongodump', $list[0]);
    }

    /**
     * Create some temp command
     *
     * @param  string $cmd
     * @return string
     */
    protected function createTempCommand($cmd)
    {
        $dir     = sys_get_temp_dir();
        $cmdPath = $dir . DIRECTORY_SEPARATOR . $cmd;

        // create the tmp executable
        file_put_contents($cmdPath, "#!/bin/bash\necho 'foo';");
        chmod($cmdPath, 0755);
        return $cmdPath;
    }

    /**
     * Remove prior created temp command
     *
     * @param string $cmdPath
     */
    protected function removeTempCommand($cmdPath)
    {
        unlink($cmdPath);
    }
}
