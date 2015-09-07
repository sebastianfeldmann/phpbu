<?php
namespace phpbu\App\Configuration\Loader;

/**
 * Configuration test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XmlTest extends \PHPUnit_Framework_TestCase
{
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
        $json   = realpath(__DIR__ . '/../../../_files/conf/config-no-xml.json');
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
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-target.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoSource()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-source.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoSourceType()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-source-type.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoLoggerType()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-logger-type.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoCleanupType()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-cleanup-type.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoCryptType()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-crypt-type.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoSyncType()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/config-no-sync-type.xml');
        $loader = new Xml($file);
        $config = $loader->getConfiguration();
        $this->assertFalse($config, 'exception should be thrown');
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testAppSettings()
    {
        $dir    = realpath(__DIR__ . '/../../../_files/conf');
        $file   = 'config-valid.xml';
        $loader = new Xml($dir . '/' . $file);
        $config = $loader->getConfiguration();

        $this->assertEquals($dir . '/backup/bootstrap.php', $config->getBootstrap());
        $this->assertEquals(true, $config->getColors());
        $this->assertEquals(false, $config->getVerbose());
    }

    /**
     * Tests Xml::getConfiguration
     */
    public function testPhpSettings()
    {
        $dir    = realpath(__DIR__ . '/../../../_files/conf');
        $file   = 'config-valid.xml';
        $loader = new Xml($dir . '/' . $file);
        $conf   = $loader->getConfiguration();
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
        $dir      = realpath(__DIR__ . '/../../../_files/conf');
        $file     = 'config-valid.xml';
        $loader   = new Xml($dir . '/' . $file);
        $conf     = $loader->getConfiguration();
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
        $this->assertEquals('MinSize', $checks[0]->type);
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
        $xml     = realpath(__DIR__ . '/../../../_files/conf/config-invalid-checks.xml');
        $loader  = new Xml($xml);
        $conf    = $loader->getConfiguration();
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
        $dir     = realpath(__DIR__ . '/../../../_files/conf');
        $file    = 'config-valid.xml';
        $loader  = new Xml($dir . '/' . $file);
        $conf    = $loader->getConfiguration();
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
        $xml     = realpath(__DIR__ . '/../../../_files/conf/config-logging.xml');
        $loader  = new Xml($xml);
        $conf    = $loader->getConfiguration();
        $loggers = $conf->getLoggers();

        $this->assertTrue(is_array($loggers));
        $this->assertEquals(1, count($loggers), 'should be exactly one logger');
    }
}
