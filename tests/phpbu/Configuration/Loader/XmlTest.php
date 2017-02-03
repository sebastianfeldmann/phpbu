<?php
namespace phpbu\App\Configuration\Loader;
use phpbu\App\Factory;

/**
 * Xml Configuration Loader test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \phpbu\App\Factory
     */
    protected static $factory;

    /**
     * Create the AppFactory
     */
    public static function setUpBeforeClass()
    {
        self::$factory = new Factory();
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNotFound()
    {
        $loader = new Xml('some.xml');
        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoXml()
    {
        $json   = PHPBU_TEST_FILES . '/conf/xml/config-no-xml.json';
        $loader = new Xml($json);
        $this->assertFalse($loader, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoTarget()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-target.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoSource()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-source.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoSourceType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-source-type.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoLoggerType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-logger-type.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoCleanupType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-cleanup-type.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoCryptType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-crypt-type.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoSyncType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-sync-type.xml';
        $loader = new Xml($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testAppSettings()
    {
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-valid.xml';
        $loader = new Xml($dir . '/' . $file);
        $config = $loader->getConfiguration(self::$factory);

        $this->assertEquals($dir . '/backup/bootstrap.php', $config->getBootstrap());
        $this->assertEquals(true, $config->getColors());
        $this->assertEquals(false, $config->getVerbose());
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testPhpSettings()
    {
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-valid.xml';
        $loader = new Xml($dir . '/' . $file);
        $conf   = $loader->getConfiguration(self::$factory);
        $ini    = $conf->getIniSettings();
        $path   = $conf->getIncludePaths();

        $this->assertEquals(0, $ini['max_execution_time']);
        $this->assertTrue(is_array($path));
        $this->assertEquals($dir . '/.', $path[0]);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testBackupSettings()
    {
        $dir      = PHPBU_TEST_FILES . '/conf/xml';
        $file     = 'config-valid.xml';
        $loader   = new Xml($dir . '/' . $file);
        $conf     = $loader->getConfiguration(self::$factory);
        $settings = $conf->getIniSettings();
        $backups  = $conf->getBackups();
        $backup   = $backups[0];
        $checks   = $backup->getChecks();
        $syncs    = $backup->getSyncs();
        $crypt    = $backup->getCrypt();
        $cleanup  = $backup->getCleanup();

        $this->assertTrue(is_array($settings));
        $this->assertTrue(is_array($backups));
        $this->assertEquals(1, count($backups), 'should be exactly one backup');
        $this->assertEquals('tarball', $backup->getName());
        $this->assertEquals(false, $backup->stopOnFailure());
        $this->assertEquals('tar', $backup->getSource()->type);
        $this->assertEquals($dir . '/backup/src', $backup->getTarget()->dirname);
        $this->assertEquals('tarball-%Y%m%d-%H%i.tar', $backup->getTarget()->filename);
        $this->assertEquals('bzip2', $backup->getTarget()->compression);
        $this->assertEquals('SizeMin', $checks[0]->type);
        $this->assertEquals('10M', $checks[0]->value);
        $this->assertEquals('mcrypt', $crypt->type);
        $this->assertEquals('sftp', $syncs[0]->type);
        $this->assertEquals('Capacity', $cleanup->type);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testBackupSettingsInvalidChecks()
    {
        $xml     = PHPBU_TEST_FILES . '/conf/xml/config-invalid-checks.xml';
        $loader  = new Xml($xml);
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];

        $this->assertTrue(is_array($conf->getIncludePaths()));
        $this->assertEquals(1, count($conf->getBackups()), 'should be exactly one backup');
        $this->assertTrue(is_array($backup->getChecks()));
        $this->assertEquals(0, count($backup->getChecks()));
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testLoggingSettings()
    {
        $dir     = PHPBU_TEST_FILES . '/conf/xml';
        $file    = 'config-valid.xml';
        $loader  = new Xml($dir . '/' . $file);
        $conf    = $loader->getConfiguration(self::$factory);
        $loggers = $conf->getLoggers();
        $log1    = $loggers[0];

        $this->assertEquals(2, count($loggers), 'should be exactly two logger');
        $this->assertEquals('json', $log1->type);
        $this->assertEquals($dir . '/backup/json.log', $log1->options['target']);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testAppLoggingSettingsWithOption()
    {
        $xml     = PHPBU_TEST_FILES . '/conf/xml/config-logging.xml';
        $loader  = new Xml($xml);
        $conf    = $loader->getConfiguration(self::$factory);
        $loggers = $conf->getLoggers();

        $this->assertTrue(is_array($loggers));
        $this->assertEquals(1, count($loggers), 'should be exactly one logger');
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testGetAdapterConfigs()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir     = PHPBU_TEST_FILES . '/conf/xml';
        $file    = 'config-valid-adapter.xml';
        $loader  = new Xml($dir . '/' . $file);
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];
        $syncs   = $backup->getSyncs();
        $sync    = $syncs[0];
        $this->assertEquals('secret', $sync->options['password']);
    }

    /**
     * Tests Xml::getConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidAdapterRef()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-no-adapter.xml';
        $loader = new Xml($dir . '/' . $file);
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::getConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidAdapterNoType()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-no-adapter-type.xml';
        $loader = new Xml($dir . '/' . $file);
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::getConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidAdapterNoName()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-no-adapter-name.xml';
        $loader = new Xml($dir . '/' . $file);
        $loader->getConfiguration(self::$factory);
    }
}
