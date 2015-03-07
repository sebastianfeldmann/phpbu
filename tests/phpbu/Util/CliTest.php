<?php
namespace phpbu\App\Util;

/**
 * Cli utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CliTest extends \PHPUnit_Framework_TestCase
{
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
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            // can't be tested on windows system
            $this->assertTrue(true);
        } else {
            // assume ls should be there
            $cmd = Cli::detectCmdLocation('someStupidCommand', '/tmp');
            $this->assertFalse(true, $cmd . ' should not be found');
        }
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
        $result  = Cli::detectCmdLocation($cmd, null, array(dirname($cmdPath)));
        // cleanup tmp executable
        $this->removeTempCommand($cmdPath);

        $this->assertEquals($cmdPath, $result, 'foo command should be found');
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
        return array(
            array('C:\foo', true),
            array('\\foo\\bar', true),
            array('..\\foo', false),
        );
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
