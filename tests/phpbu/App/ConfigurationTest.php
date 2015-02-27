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
 * @since      Class available since Release 1.0.5
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Configuration::loadXmlFile
     * 
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNotFound()
    {
        $conf = new Configuration('some.xml');
        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Configuration::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFileNoXml()
    {
        $dir  = realpath(__DIR__ . '/../../_files/conf');
        $file = 'config-no-target.xml';
        $conf = new Configuration('some.xml');
        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Configuration::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoTarget()
    {
        $conf = new Configuration('some.xml');
        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Configuration::loadXmlFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupNoSource()
    {
        $conf = new Configuration('some.xml');
        $this->assertFalse(true, 'exception should be thrown');
    }
    
    /**
     * Tests Configuration::getAppSettings
     */
    public function testAppSettings()
    {
        $dir  = realpath(__DIR__ . '/../../_files/conf');
        $file = 'config-valid.xml';
        $conf = new Configuration($dir . '/' . $file);

        $settings = $conf->getAppSettings();
        
        $this->assertEquals($dir . '/backup/bootstrap.php', $settings['bootstrap']);
        $this->assertEquals(true, $settings['colors']);
        $this->assertEquals(false, $settings['verbose']);
    }

    /**
     * Tests Configuration::getPhpSettings
     */
    public function testPhpSettings()
    {
        $dir  = realpath(__DIR__ . '/../../_files/conf');
        $file = 'config-valid.xml';
        $conf = new Configuration($dir . '/' . $file);

        $settings = $conf->getPhpSettings();

        $this->assertEquals(0, $settings['ini']['max_execution_time']);
        $this->assertTrue(is_array($settings['include_path']));
        $this->assertEquals($dir . '/.', $settings['include_path'][0]);
    }

    /**
     * Tests Configuration::getAppSettings
     */
    public function testBackupSettings()
    {
        $dir  = realpath(__DIR__ . '/../../_files/conf');
        $file = 'config-valid.xml';
        $conf = new Configuration($dir . '/' . $file);

        $settings = $conf->getBackupSettings();

        $this->assertTrue(is_array($settings));
        $this->assertEquals(1, count($settings), 'should be exactly one backup');
        $this->assertEquals('tarball', $settings[0]['name']);
        $this->assertEquals(false, $settings[0]['stopOnError']);
        $this->assertEquals('tar', $settings[0]['source']['type']);
        $this->assertEquals($dir . '/backup/src', $settings[0]['target']['dirname']);
        $this->assertEquals('tarball-%Y%m%d-%H%i.tar', $settings[0]['target']['filename']);
        $this->assertEquals('bzip2', $settings[0]['target']['compress']);
        $this->assertEquals('MinSize', $settings[0]['checks'][0]['type']);
        $this->assertEquals('10M', $settings[0]['checks'][0]['value']);
        $this->assertEquals('sftp', $settings[0]['syncs'][0]['type']);
        $this->assertEquals('Capacity', $settings[0]['cleanup']['type']);
    }

    /**
     * Tests Configuration::getAppSettings
     */
    public function testLoggingSettings()
    {
        $dir  = realpath(__DIR__ . '/../../_files/conf');
        $file = 'config-valid.xml';
        $conf = new Configuration($dir . '/' . $file);

        $settings = $conf->getLoggingSettings();
        
        $this->assertTrue(is_array($settings));
        $this->assertEquals(2, count($settings), 'should be exactly one logger');
        $this->assertEquals('json', $settings[0]['type']);
        $this->assertEquals($dir . '/backup/json.log', $settings[0]['options']['target']);
    }
}
