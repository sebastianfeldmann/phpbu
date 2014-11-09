<?php
namespace phpbu\Util;

/**
 * Cli utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test detectCmdLocation
     */
    public function testdetectCmdLocationWhich()
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
    public function testdetectCmdLocationWithProvidedPath()
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
    public function testdetectCmdLocationWithOptionalLocation()
    {
        $cmd     = 'bar';
        $cmdPath = $this->createTempCommand($cmd);
        $result  = Cli::detectCmdLocation($cmd, null, array(dirname($cmdPath)));
        // cleanup tmp executable
        $this->removeTempCommand($cmdPath);

        $this->assertEquals($cmdPath, $result, 'foo command should be found');
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
