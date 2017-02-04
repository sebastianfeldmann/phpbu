<?php
namespace phpbu\App\Configuration\Loader;
use phpbu\App\Factory;

/**
 * Json Configuration Loader test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class JsonTest extends \PHPUnit\Framework\TestCase
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
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNotFound()
    {
        $loader = new Json('some.json');
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoJson()
    {
        $json   = PHPBU_TEST_FILES . '/conf/json/config-no-json.xml';
        $loader = new Json($json);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoBackup()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-backup.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoTarget()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-target.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoSource()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-source.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoSourceType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-source-type.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoLoggerType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-logger-type.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoCleanupType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-cleanup-type.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoCryptType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-crypt-type.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoSyncType()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-sync-type.json';
        $loader = new Json($file);
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testAppSettings()
    {
        $dir    = PHPBU_TEST_FILES . '/conf/json';
        $file   = 'config-valid.json';
        $loader = new Json($dir . '/' . $file);
        $config = $loader->getConfiguration(self::$factory);

        $this->assertEquals($dir . '/backup/bootstrap.php', $config->getBootstrap());
        $this->assertEquals(true, $config->getColors());
        $this->assertEquals(false, $config->getVerbose());
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testBackupSettings()
    {
        $dir      = PHPBU_TEST_FILES . '/conf/json';
        $file     = 'config-valid.json';
        $loader   = new Json($dir . '/' . $file);
        $conf     = $loader->getConfiguration(self::$factory);
        $backups  = $conf->getBackups();
        $backup   = $backups[0];
        $checks   = $backup->getChecks();
        $syncs    = $backup->getSyncs();
        $crypt    = $backup->getCrypt();
        $cleanup  = $backup->getCleanup();

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
        $this->assertEquals('openssl', $crypt->type);
        $this->assertEquals('dropbox', $syncs[0]->type);
        $this->assertEquals('Capacity', $cleanup->type);
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testBackupSettingsInvalidChecks()
    {
        $json    = PHPBU_TEST_FILES . '/conf/json/config-invalid-checks.json';
        $loader  = new Json($json);
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];

        $this->assertEquals(1, count($conf->getBackups()), 'should be exactly one backup');
        $this->assertTrue(is_array($backup->getChecks()));
        $this->assertEquals(0, count($backup->getChecks()));
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testLoggingSettings()
    {
        $dir     = PHPBU_TEST_FILES . '/conf/json';
        $file    = 'config-valid.json';
        $loader  = new Json($dir . '/' . $file);
        $conf    = $loader->getConfiguration(self::$factory);
        $loggers = $conf->getLoggers();
        $log1    = $loggers[0];

        $this->assertEquals(2, count($loggers), 'should be exactly two logger');
        $this->assertEquals('json', $log1->type);
        $this->assertEquals($dir . '/backup/json.log', $log1->options['target']);
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testAppLoggingSettingsWithOption()
    {
        $file    = PHPBU_TEST_FILES . '/conf/json/config-logging.json';
        $loader  = new Json($file);
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
        $file    = PHPBU_TEST_FILES . '/conf/json/config-valid-adapter.json';
        $loader  = new Json($file);
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];
        $syncs   = $backup->getSyncs();
        $sync    = $syncs[0];
        $this->assertEquals('secret', $sync->options['token']);
    }

    /**
     * Tests Xml::getConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testGetAdapterInvalidNoType()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $file    = PHPBU_TEST_FILES . '/conf/json/config-no-adapter-type.json';
        $loader  = new Json($file);
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::getConfiguration
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testGetAdapterInvalidNoName()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $file    = PHPBU_TEST_FILES . '/conf/json/config-no-adapter-name.json';
        $loader  = new Json($file);
        $loader->getConfiguration(self::$factory);
    }
}
