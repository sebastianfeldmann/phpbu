<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Configuration\Bootstrapper;
use phpbu\App\Factory;
use PHPUnit\Framework\TestCase;

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
class JsonTest extends TestCase
{
    /**
     * @var \phpbu\App\Factory
     */
    protected static $factory;

    /**
     * Create the AppFactory
     */
    public static function setUpBeforeClass(): void
    {
        self::$factory = new Factory();
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNotFound()
    {
        $this->expectException('phpbu\App\Exception');
        $loader = new Json('some.json', $this->getBootstrapperMock());
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNoJson()
    {
        $this->expectException('phpbu\App\Exception');
        $json   = PHPBU_TEST_FILES . '/conf/json/config-no-json.xml';
        $loader = new Json($json, $this->getBootstrapperMock());
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testBackupNoBackup()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-backup.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testBackupNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-target.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testBackupNoSource()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-source.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNoSourceType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-source-type.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNoLoggerType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-logger-type.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNoCleanupType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-cleanup-type.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNoCryptType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-crypt-type.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::loadJsonFile
     */
    public function testFileNoSyncType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/json/config-no-sync-type.json';
        $loader = new Json($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testAppSettings()
    {
        $dir    = PHPBU_TEST_FILES . '/conf/json';
        $file   = 'config-valid.json';
        $loader = new Json($dir . '/' . $file, $this->getBootstrapperMock(true));
        $config = $loader->getConfiguration(self::$factory);

        $this->assertEquals($dir . '/backup/bootstrap.php', $config->getBootstrap());
        $this->assertTrue($config->getColors());
        $this->assertFalse($config->getVerbose());
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testBackupSettings()
    {
        $dir      = PHPBU_TEST_FILES . '/conf/json';
        $file     = 'config-valid.json';
        $loader   = new Json($dir . '/' . $file, $this->getBootstrapperMock());
        $conf     = $loader->getConfiguration(self::$factory);
        $backups  = $conf->getBackups();
        $backup   = $backups[0];
        $checks   = $backup->getChecks();
        $syncs    = $backup->getSyncs();
        $crypt    = $backup->getCrypt();
        $cleanup  = $backup->getCleanup();

        $this->assertIsArray($backups);
        $this->assertCount(1, $backups, 'should be exactly one backup');
        $this->assertEquals('tarball', $backup->getName());
        $this->assertFalse($backup->stopOnFailure());
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
        $loader  = new Json($json, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];

        $this->assertCount(1, $conf->getBackups(), 'should be exactly one backup');
        $this->assertIsArray($backup->getChecks());
        $this->assertCount(0, $backup->getChecks());
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testLoggingSettings()
    {
        $dir     = PHPBU_TEST_FILES . '/conf/json';
        $file    = 'config-valid.json';
        $loader  = new Json($dir . '/' . $file, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $loggers = $conf->getLoggers();
        $log1    = $loggers[0];

        $this->assertCount(2, $loggers, 'should be exactly two logger');
        $this->assertEquals('json', $log1->type);
        $this->assertEquals($dir . '/backup/json.log', $log1->options['target']);
    }

    /**
     * Tests Json::getConfiguration
     */
    public function testAppLoggingSettingsWithOption()
    {
        $file    = PHPBU_TEST_FILES . '/conf/json/config-logging.json';
        $loader  = new Json($file, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $loggers = $conf->getLoggers();

        $this->assertIsArray($loggers);
        $this->assertCount(1, $loggers, 'should be exactly one logger');
    }


    /**
 * Tests Xml::getConfiguration
 */
    public function testGetAdapterConfigs()
    {
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $file    = PHPBU_TEST_FILES . '/conf/json/config-valid-adapter.json';
        $loader  = new Json($file, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];
        $syncs   = $backup->getSyncs();
        $sync    = $syncs[0];
        $this->assertEquals('secret', $sync->options['token']);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testGetAdapterInvalidNoType()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $file    = PHPBU_TEST_FILES . '/conf/json/config-no-adapter-type.json';
        $loader  = new Json($file, $this->getBootstrapperMock());
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testGetAdapterInvalidNoName()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $file    = PHPBU_TEST_FILES . '/conf/json/config-no-adapter-name.json';
        $loader  = new Json($file, $this->getBootstrapperMock());
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Return Bootstrapper mock.
     *
     * @param  bool $execute
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getBootstrapperMock(bool $execute = false)
    {
        $mock = $crypter = $this->createMock(Bootstrapper::class);
        if ($execute) {
            $mock->expects($this->once())->method('run');
        }
        return $mock;
    }
}
