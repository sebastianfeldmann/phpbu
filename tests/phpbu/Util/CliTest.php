<?php
namespace phpbu\App\Util;

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
class CliTest extends \PHPUnit\Framework\TestCase
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
    public function setUp()
    {
        self::$server = $_SERVER;
    }

    /**
     * Restore $_SERVER settings.
     */
    public function tearDown()
    {
        $_SERVER = self::$server;
    }

    /**
     * Test detectCmdLocation Exception
     *
     * @expectedException \RuntimeException
     */
    public function testDetectCmdFail()
    {
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
     *
     * @expectedException \RuntimeException
     */
    public function testDetectCmdFailWithPath()
    {
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
     *
     * @expectedException \RuntimeException
     */
    public function testGetEnvPathFail()
    {
        unset($_SERVER['PATH']);
        unset($_SERVER['Path']);
        unset($_SERVER['path']);
        $path = Cli::getEnvPath();
    }

    /**
     * Tests Cli::isAbsolutePath
     */
    public function testIsAbsolutePathTrue()
    {
        $path = '/foo/bar';
        $res  = Cli::isAbsolutePath($path);

        $this->assertTrue($res, 'should be detected as absolute path');
    }

    /**
     * Tests Cli::isAbsolutePath
     */
    public function testIsAbsolutePathFalse()
    {
        $path = '../foo/bar';
        $res  = Cli::isAbsolutePath($path);

        $this->assertFalse($res, 'should not be detected as absolute path');
    }

    /**
     * Tests Cli::isAbsolutePath
     */
    public function testIsAbsolutePathStream()
    {
        $path = 'php://foo/bar';
        $res  = Cli::isAbsolutePath($path);

        $this->assertTrue($res, 'should not be detected as absolute path');
    }

    /**
     * Tests Cli::isAbsolutePathWindows
     *
     * @dataProvider providerWindowsPaths
     *
     * @param string  $path
     * @param boolean $expected
     */
    public function testIsAbsolutePathWindows($path, $expected)
    {
        $res = Cli::isAbsoluteWindowsPath($path);

        $this->assertEquals($expected, $res, 'should be detected as expected');
    }

    /**
     * Tests Cli::toAbsolutePath
     */
    public function testToAbsolutePathAlreadyAbsolute()
    {
        $res = Cli::toAbsolutePath('/foo/bar', '');

        $this->assertEquals('/foo/bar', $res, 'should be detected as absolute');
    }

    /**
     * Data provider testIsAbsolutePathWindows.
     *
     * @return return array
     */
    public function providerWindowsPaths()
    {
        return [
            ['C:\foo', true],
            ['\\foo\\bar', true],
            ['..\\foo', false],
        ];
    }

    /**
     * Tests Cli::toAbsolutePath
     */
    public function testToAbsolutePathWIthIncludePath()
    {
        $filesDir = PHPBU_TEST_FILES . '/conf/xml';
        set_include_path(get_include_path() . PATH_SEPARATOR . $filesDir);
        $res = Cli::toAbsolutePath('config-valid.xml', '', true);

        $this->assertEquals($filesDir . '/config-valid.xml', $res);
    }

    /**
     * Tests Cli::formatWithColor
     */
    public function testFormatWithColor()
    {
        $plainText   = 'Mein Test';
        $coloredText = Cli::formatWithColor('fg-black, bg-green', $plainText);

        $this->assertTrue(strpos($coloredText, "\x1b[0m") !== false);
    }

    /**
     * Tests Cli::formatWithColor
     */
    public function testFormatWithColorEmptyLine()
    {
        $plainText   = '';
        $coloredText = Cli::formatWithColor('fg-black, bg-green', $plainText);

        $this->assertTrue(strpos($coloredText, "\x1b[0m") === false);
    }

    /**
     * Tests Cli::formatWithAsterisk
     */
    public function testFormatWithAsterisk()
    {
        $plainText     = 'Mein Test ';
        $decoratedText = Cli::formatWithAsterisk($plainText);

        $this->assertEquals(75, strlen(trim($decoratedText)));
        $this->assertTrue(strpos($decoratedText, '*') !== false);
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
        $dirToDelete = $dir . '/foo';
        $subDir      = $dirToDelete . '/bar';

        $file        = $dirToDelete . '/fiz.txt';
        $fileInSub   = $subDir . '/baz.txt';

        mkdir($subDir, 0700, true);
        file_put_contents($file, 'fiz');
        file_put_contents($fileInSub, 'baz');

        Cli::removeDir($dirToDelete);

        $this->assertFalse(file_exists($file));
        $this->assertFalse(file_exists($fileInSub));
        $this->assertFalse(file_exists($subDir));
        $this->assertFalse(file_exists($dirToDelete));
    }

    /**
     * Tests Cli::getCommandLocations
     */
    public function testCommandLocationsDefault()
    {
        $list = Cli::getCommandLocations('tar');
        $this->assertEquals(0, count($list));

        $list = Cli::getCommandLocations('mysqldump');
        $this->assertEquals(2, count($list));
    }

    /**
     * Tests Cli::getCommandLocations
     */
    public function testAddCommandLocations()
    {
        Cli::addCommandLocation('mongodump', '/foo/mongodump');
        $list = Cli::getCommandLocations('mongodump');

        $this->assertEquals(1, count($list));
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
