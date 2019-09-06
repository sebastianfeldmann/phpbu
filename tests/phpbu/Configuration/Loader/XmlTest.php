<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Configuration\Bootstrapper;
use phpbu\App\Exception;
use phpbu\App\Factory;
use PHPUnit\Framework\TestCase;

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
class XmlTest extends TestCase
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
     * Tests Xml::loadXmlFile
     */
    public function testFileNotFound()
    {
        $this->expectException(Exception::class);
        $loader = new Xml('some.xml', $this->getBootstrapperMock());
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testFileNoXml()
    {
        $this->expectException('phpbu\App\Exception');
        $json   = PHPBU_TEST_FILES . '/conf/xml/config-no-xml.json';
        $loader = new Xml($json, $this->getBootstrapperMock());
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testBackupNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-target.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testBackupNoSource()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-source.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testFileNoSourceType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-source-type.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testFileNoLoggerType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-logger-type.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testFileNoCleanupType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-cleanup-type.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testFileNoCryptType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-crypt-type.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::loadXmlFile
     */
    public function testFileNoSyncType()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = PHPBU_TEST_FILES . '/conf/xml/config-no-sync-type.xml';
        $loader = new Xml($file, $this->getBootstrapperMock());
        $config = $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Custom Adapter handling
     */
    public function testCustomAdapter()
    {
        $config = PHPBU_TEST_FILES . '/conf/xml/config-custom-adapter.xml';
        $loader = new Xml($config, new Bootstrapper());
        $config = $loader->getConfiguration(self::$factory);

        $this->assertEquals(PHPBU_TEST_FILES . '/conf/xml/../../misc/bootstrap.adapter.php', $config->getBootstrap());

        $backups = $config->getBackups();
        $backup  = $backups[0];
        $source  = $backup->getSource();

        $this->assertEquals('demo', $source->options['password']);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testAppSettings()
    {
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-valid.xml';
        $loader = new Xml($dir . '/' . $file, $this->getBootstrapperMock(true));
        $config = $loader->getConfiguration(self::$factory);

        $this->assertFalse($loader->hasValidationErrors());
        $this->assertCount(0, $loader->getValidationErrors());
        $this->assertEquals($dir . '/backup/bootstrap.php', $config->getBootstrap());
        $this->assertEquals(true, $config->getColors());
        $this->assertEquals(false, $config->getVerbose());
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testBackupSettings()
    {
        $dir     = PHPBU_TEST_FILES . '/conf/xml';
        $file    = 'config-valid.xml';
        $loader  = new Xml($dir . '/' . $file, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];
        $checks  = $backup->getChecks();
        $syncs   = $backup->getSyncs();
        $crypt   = $backup->getCrypt();
        $cleanup = $backup->getCleanup();

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
        $loader  = new Xml($xml, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];

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
        $loader  = new Xml($dir . '/' . $file, $this->getBootstrapperMock());
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
        $loader  = new Xml($xml, $this->getBootstrapperMock());
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
        $loader  = new Xml($dir . '/' . $file, $this->getBootstrapperMock());
        $conf    = $loader->getConfiguration(self::$factory);
        $backups = $conf->getBackups();
        $backup  = $backups[0];
        $syncs   = $backup->getSyncs();
        $sync    = $syncs[0];
        $this->assertEquals('secret', $sync->options['password']);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testInvalidAdapterRef()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-no-adapter.xml';
        $loader = new Xml($dir . '/' . $file, $this->getBootstrapperMock());
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testInvalidAdapterNoType()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-no-adapter-type.xml';
        $loader = new Xml($dir . '/' . $file, $this->getBootstrapperMock());
        $loader->getConfiguration(self::$factory);
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testInvalidAdapterNoName()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('adapter', 'fake', '\\phpbu\\App\\FakeAdapter', true);
        $dir    = PHPBU_TEST_FILES . '/conf/xml';
        $file   = 'config-no-adapter-name.xml';
        $loader = new Xml($dir . '/' . $file, $this->getBootstrapperMock());
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
