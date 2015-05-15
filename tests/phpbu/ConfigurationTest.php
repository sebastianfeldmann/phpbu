<?php
namespace phpbu\App;

/**
 * Configuration test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Configuration::setVerbose
     */
    public function testVerbose()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals(false, $conf->getVerbose());
        $conf->setVerbose(true);
        $this->assertEquals(true, $conf->getVerbose());
    }

    /**
     * Tests Configuration::setColors
     */
    public function testColors()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals(false, $conf->getColors());
        $conf->setColors(true);
        $this->assertEquals(true, $conf->getColors());
    }

    /**
     * Tests Configuration::setDebug
     */
    public function testDebug()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals(false, $conf->getDebug());
        $conf->setDebug(true);
        $this->assertEquals(true, $conf->getDebug());
    }

    /**
     * Tests Configuration::setBootstrap
     */
    public function testBootstrap()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals(null, $conf->getBootstrap());
        $conf->setBootstrap('file.php');
        $this->assertEquals('file.php', $conf->getBootstrap());
    }

    /**
     * Tests Configuration::addIncludePath
     */
    public function testIncludePath()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals(array(), $conf->getIncludePaths());
        $conf->addIncludePath('/tmp');
        $this->assertEquals(1, count($conf->getIncludePaths()));
    }

    /**
     * Tests Configuration::addIniSettings
     */
    public function testIniSettings()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals(array(), $conf->getIniSettings());
        $conf->addIniSetting('max_execution_time', 0);
        $this->assertEquals(1, count($conf->getIniSettings()));
    }

    /**
     * Tests Configuration::getWorkingDirectory
     */
    public function testGetWorkingDirectory()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals('/tmp/foo.xml', $conf->getFilename());
        $this->assertEquals('/tmp', $conf->getWorkingDirectory());
    }

    /**
     * Tests Configuration::addBackup
     */
    public function testBackup()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $backup = new Configuration\Backup('backup', true);
        $this->assertEquals(array(), $conf->getBackups());
        $conf->addBackup($backup);
        $this->assertEquals(1, count($conf->getBackups()));
    }

    /**
     * Tests Configuration::addLogger
     */
    public function testLogger()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $logger = new Configuration\Logger('json', array());
        $this->assertEquals(array(), $conf->getLoggers());
        $conf->addLogger($logger);
        $this->assertEquals(1, count($conf->getLoggers()));
    }
}
