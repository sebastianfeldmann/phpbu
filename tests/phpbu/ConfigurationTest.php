<?php
namespace phpbu\App;

use PHPUnit\Framework\TestCase;

/**
 * Configuration test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class ConfigurationTest extends TestCase
{
    /**
     * Tests Configuration::getWorkingDirectory
     */
    public function testGetWorkingDirectoryEmpty()
    {
        Configuration::setWorkingDirectory('');
        $this->assertEquals(getcwd(), Configuration::getWorkingDirectory());
    }

    /**
     * Tests Configuration::getWorkingDirectory
     */
    public function testGetWorkingDirectory()
    {
        Configuration::setWorkingDirectory('/my-wd');
        $this->assertEquals('/my-wd', Configuration::getWorkingDirectory());
    }

    /**
     * Tests Configuration::setVerbose
     */
    public function testVerbose()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertFalse($conf->getVerbose());
        $conf->setVerbose(true);
        $this->assertTrue($conf->getVerbose());
    }

    /**
     * Tests Configuration::setColors
     */
    public function testColors()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertFalse($conf->getColors());
        $conf->setColors(true);
        $this->assertTrue($conf->getColors());
    }

    /**
     * Tests Configuration::setDebug
     */
    public function testDebug()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertFalse($conf->getDebug());
        $conf->setDebug(true);
        $this->assertTrue($conf->getDebug());
    }

    /**
     * Tests Configuration::setRestore
     */
    public function testRestore()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertFalse($conf->isRestore());
        $conf->setRestore(true);
        $this->assertTrue($conf->isRestore());
    }

    /**
     * Tests Configuration::setSimulate
     */
    public function testSimulate()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertFalse($conf->isSimulation());
        $conf->setSimulate(true);
        $this->assertTrue($conf->isSimulation());
    }

    /**
     * Tests Configuration::setBootstrap
     */
    public function testBootstrap()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals('', $conf->getBootstrap());
        $conf->setBootstrap('file.php');
        $this->assertEquals('file.php', $conf->getBootstrap());
    }

    /**
     * Tests Configuration::setLimit, Configuration::isBackupActive
     */
    public function testLimit()
    {
        $conf = new Configuration();
        $conf->setLimit(['foo', 'bar']);

        $this->assertTrue($conf->isBackupActive('foo'));
        $this->assertTrue($conf->isBackupActive('bar'));
        $this->assertFalse($conf->isBackupActive('baz'));
    }

    /**
     * Tests Configuration::getWorkingDirectory
     */
    public function testGetWorkingDirectoryFromFilename()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $this->assertEquals('/tmp/foo.xml', $conf->getFilename());
        $this->assertEquals('/tmp', Configuration::getWorkingDirectory());
    }

    /**
     * Tests Configuration::setWorkingDirectory
     */
    public function testSetWorkingDirectory()
    {
        $conf = new Configuration();
        $conf->setWorkingDirectory('/tmp');
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
        $this->assertEquals([], $conf->getBackups());
        $conf->addBackup($backup);
        $this->assertCount(1, $conf->getBackups());
    }

    /**
     * Tests Configuration::addLogger
     */
    public function testLoggerConfiguration()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $logger = new Configuration\Logger('json', []);
        $this->assertEquals([], $conf->getLoggers());
        $conf->addLogger($logger);
        $this->assertCount(1, $conf->getLoggers());
    }

    /**
     * Tests Configuration::addLogger
     */
    public function testLoggerListener()
    {
        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $logger = new Result\PrinterCli(false, false, false);
        $this->assertEquals([], $conf->getLoggers());
        $conf->addLogger($logger);
        $this->assertCount(1, $conf->getLoggers());
    }

    /**
     * Tests Configuration::addLogger
     */
    public function testLoggerInvalid()
    {
        $this->expectException(Exception::class);

        $conf = new Configuration();
        $conf->setFilename('/tmp/foo.xml');
        $conf->addLogger('no valid logger at all');
    }
}
