<?php
namespace phpbu\App;

use phpbu\App\Backup\Check;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Log\Logger;

/**
 * Factory test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
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

        $this->assertEquals('phpbu\\App\\phpbuAppFactoryTestSource', get_class($source), 'classes should match');
    }

    /**
     * Tests Factory::createLogger
     */
    public function testCreateLogger()
    {
        $logger = Factory::createLogger('mail', array('recipients' => 'no-reply@phpbu.de'));

        $this->assertEquals('phpbu\\App\\Log\\Mail', get_class($logger), 'classes should match');
    }

    /**
     * Tests Factory::createCheck
     */
    public function testCreateCheck()
    {
        $check = Factory::createCheck('sizemin');

        $this->assertEquals('phpbu\\App\\Backup\\Check\\SizeMin', get_class($check), 'classes should match');
    }

    /**
     * Tests Factory::createCrypter
     */
    public function testCreateCrypter()
    {
        Factory::register('crypter', 'dummy', '\\phpbu\\App\\phpbuAppFactoryTestCrypter');

        $crypter = Factory::createCrypter('dummy', array('algorithm' => 'blowfish', 'key' => 'fooBarBaz'));

        $this->assertEquals('phpbu\\App\\phpbuAppFactoryTestCrypter', get_class($crypter), 'classes should match');
    }

    /**
     * Tests Factory::createSync
     */
    public function testCreateSync()
    {
        $sync = Factory::createSync('Rsync', array('args' => 'foo'));

        $this->assertEquals('phpbu\\App\\Backup\\Sync\\Rsync', get_class($sync), 'classes should match');
    }

    /**
     * Tests Factory::createCleaner
     */
    public function testCreateCleaner()
    {
        $sync = Factory::createCleaner('Capacity', array('size' => '10M'));

        $this->assertEquals('phpbu\\App\\Backup\\Cleaner\\Capacity', get_class($sync), 'classes should match');
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

        $this->assertEquals('phpbu\\App\\phpbuAppFactoryTestCheck', get_class($dummy), 'Factory should create dummy object');
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
     * Tests Factory::createSource
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateSourceThatIsNone()
    {
        Factory::register('source', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestNothing', true);

        $source = Factory::createSource('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createSource
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateCrypterThatIsNone()
    {
        Factory::register('crypter', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestNothing', true);

        $crypter = Factory::createCrypter('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createLogger
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateLoggerThatIsNone()
    {
        Factory::register('logger', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestNothing', true);

        $logger = Factory::createLogger('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createLogger
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateLoggerThatIsLoggerButNoListener()
    {
        Factory::register('logger', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestLoggerButNoListener', true);

        $logger = Factory::createLogger('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createCleaner
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateCleanerThatIsNone()
    {
        Factory::register('cleaner', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestNothing', true);

        $cleaner = Factory::createCleaner('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createCleaner
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateSyncThatIsNone()
    {
        Factory::register('sync', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestNothing', true);

        $sync = Factory::createSync('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createCleaner
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateCheckThatIsNone()
    {
        Factory::register('check', 'nothing', '\\phpbu\\App\\phpbuAppFactoryTestNothing', true);

        $check = Factory::createCheck('nothing');

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
     * @param  \phpbu\App\Backup\Target $target
     * @param  string                   $value
     * @param  \phpbu\App\Backup\Collector
     * @param  \phpbu\App\Result
     * @return boolean
     */
    public function pass(Target $target, $value, Collector $collector, Result $result)
    {
        // do something fooish
    }
}

/**
 * Class phpbuAppFactoryTestNothing
 */
class phpbuAppFactoryTestNothing
{
    /**
     * Do nothing.
     */
    public function doNothing()
    {
        // do something fooish
    }
}

/**
 * Class phpbuAppFactoryTestNothing
 */
class phpbuAppFactoryTestCrypter implements Crypter
{
    /**
     * Do nothing.
     */
    public function doNothing()
    {
        // do something fooish
    }

    /**
     * Setup the Crypter.
     *
     * @param array $options
     */
    public function setup(array $options = array())
    {
        // do something fooish
    }

    /**
     * Checks the created backup.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function crypt(Target $target, Result $result)
    {
        // do something fooish
    }

    /**
     * Return the encrypted file suffix.
     *
     * @return string
     */
    public function getSuffix()
    {
        return 'mc';
    }
}

/**
 * Class phpbuAppFactoryTestNothing
 */
class phpbuAppFactoryTestLoggerButNoListener implements Logger
{
    /**
     * Setup the logger.
     *
     * @param array $options
     */
    public function setup(array $options)
    {
        // do something fooish
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array();
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
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     */
    public function backup(Target $target, Result $result)
    {
        // do something fooish
    }
}
