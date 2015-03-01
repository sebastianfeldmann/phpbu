<?php
namespace phpbu\App;

use phpbu\Backup\Check;
use phpbu\Backup\Collector;
use phpbu\Backup\Source;
use phpbu\Backup\Target;

/**
 * Args parser test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.5
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Factory::createSource
     */
    public function testCreateSource()
    {
        // register dummy source, all default sources have system dependencies like cli binaries
        Factory::register('source', 'dummy', '\\phpbu\\App\\phpbuAppFactoryTestSource');
        $source = Factory::createSource('dummy', array());

        $this->assertEquals(get_class($source), 'phpbu\\App\\phpbuAppFactoryTestSource', 'classes should match');
    }

    /**
     * Tests Factory::createLogger
     */
    public function testCreateLogger()
    {
        $logger = Factory::createLogger('mail', array('recipients' => 'no-reply@phpbu.de'));

        $this->assertEquals(get_class($logger), 'phpbu\\Log\\Mail', 'classes should match');
    }

    /**
     * Tests Factory::createCheck
     */
    public function testCreateCheck()
    {
        $check = Factory::createCheck('sizemin');

        $this->assertEquals(get_class($check), 'phpbu\\Backup\\Check\\SizeMin', 'classes should match');
    }

    /**
     * Tests Factory::createSync
     */
    public function testCreateSync()
    {
        $sync = Factory::createSync('Rsync', array('args' => 'foo'));

        $this->assertEquals(get_class($sync), 'phpbu\\Backup\\Sync\\Rsync', 'classes should match');
    }

    /**
     * Tests Factory::createCleaner
     */
    public function testCreateCleaner()
    {
        $sync = Factory::createCleaner('Capacity', array('size' => '10M'));

        $this->assertEquals(get_class($sync), 'phpbu\\Backup\\Cleaner\\Capacity', 'classes should match');
    }

    /**
     * Tests Factory::createType
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateUnknown()
    {
        $sync = Factory::create('sync', 'Unknown', array('foo' => 'bar'));

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Factory::register
     */
    public function testRegisterCheckOk()
    {
        Factory::register('check', 'dummy', '\\phpbu\\App\\phpbuAppFactoryTestCheck');

        $dummy = Factory::create('check', 'dummy');

        $this->assertEquals(get_class($dummy), 'phpbu\\App\\phpbuAppFactoryTestCheck', 'Factory should create dummy object');
    }

    /**
     * Tests Factory::register
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testRegisterInvalidType()
    {
        Factory::register('invalid', 'dummy', '\\phpbu\\App\\phpbuAppFactoryTestCheck');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::register
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testRegisterExistingCheck()
    {
        Factory::register('check', 'sizemin', '\\phpbu\\App\\phpbuAppFactoryTestCheck');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::register
     *
     * @depends testRegisterExistingCheck
     */
    public function testRegisterExistingCheckForce()
    {
        Factory::register('check', 'sizemin', '\\phpbu\\App\\phpbuAppFactoryTestCheck', true);

        $dummy = Factory::create('check', 'sizemin');

        $this->assertEquals(get_class($dummy), 'phpbu\\App\\phpbuAppFactoryTestCheck', 'Factory should create dummy object');
    }
}

/**
 * Class phpbuAppFactoryTestObject
 */
class phpbuAppFactoryTestCheck implements Check
{
    /**
     * Checks the created backup.
     *
     * @param  \phpbu\Backup\Target $target
     * @param  string $value
     * @param  \phpbu\Backup\Collector
     * @param  \phpbu\App\Result
     * @return boolean
     */
    public function pass(Target $target, $value, Collector $collector, Result $result)
    {
        // do something fooish
    }
}

/**
 * Class phpbuAppFactoryTestObject
 */
class phpbuAppFactoryTestSource implements Source
{
    /**
     * Setup the source.
     *
     * @param array $conf
     */
    public function setup(array $conf = array())
    {
        // do something fooish
    }

    /**
     * Runner the backup
     *
     * @param  \phpbu\Backup\Target $target
     * @param  \phpbu\App\Result $result
     */
    public function backup(Target $target, Result $result)
    {
        // do something fooish
    }
}
