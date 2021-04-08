<?php
namespace phpbu\App;

use PHPUnit\Framework\TestCase;

/**
 * Factory test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class FactoryTest extends TestCase
{
    /**
     * Tests Factory::createAdapter
     */
    public function testCreateAdapter()
    {
        $factory = new Factory();
        $adapter = $factory->createAdapter('env', []);

        $this->assertInstanceOf('phpbu\\App\\Adapter\\Env', $adapter, 'adapter classes should match');
    }

    /**
     * Tests Factory::createTarget
     */
    public function testCreateTarget()
    {
        $directory = sys_get_temp_dir() . '/test-dir';
        $conf      = new Configuration\Backup\Target($directory, 'test-file', 'bzip2');
        $factory   = new Factory();
        $target    = $factory->createTarget($conf);

        $this->assertInstanceOf('phpbu\\App\\Backup\\Target', $target, 'should be a target');
        $this->assertEquals('test-file.bz2', $target->getFilename());
        $this->assertEquals($directory . '/test-file.bz2', $target->getPathname());

        $this->assertFileExists($directory);

        rmdir($directory);
    }

    /**
     * Tests Factory::createSource
     */
    public function testCreateSource()
    {
        // register dummy source, all default sources have system dependencies like cli binaries
        Factory::register('source', 'dummy', '\\phpbu\\App\\Backup\\Source\\FakeSource');
        $factory = new Factory();
        $source  = $factory->createSource('dummy', []);

        $this->assertInstanceOf('phpbu\\App\\Backup\\Source\\FakeSource', $source, 'classes should match');
    }

    /**
     * Tests Factory::createLogger
     */
    public function testCreateLogger()
    {
        $factory = new Factory();
        $logger  = $factory->createLogger('mail', ['recipients' => 'no-reply@phpbu.de']);

        $this->assertInstanceOf('phpbu\\App\\Log\\Mail', $logger, 'classes should match');
    }

    /**
     * Tests Factory::createCheck
     */
    public function testCreateCheck()
    {
        $factory = new Factory();
        $check  = $factory->createCheck('sizemin');

        $this->assertInstanceOf('phpbu\\App\\Backup\\Check\\SizeMin', $check, 'classes should match');
    }

    /**
     * Tests Factory::createCrypter
     */
    public function testCreateCrypter()
    {
        Factory::register('crypter', 'dummy', '\\phpbu\\App\\Backup\\Crypter\\FakeCrypter');

        $factory = new Factory();
        $crypter = $factory->createCrypter('dummy', []);

        $this->assertInstanceOf('phpbu\\App\\Backup\\Crypter\\FakeCrypter', $crypter, 'classes should match');
    }

    /**
     * Tests Factory::createSync
     */
    public function testCreateSync()
    {
        $factory = new Factory();
        $sync    = $factory->createSync('Rsync', ['args' => 'foo']);

        $this->assertInstanceOf('phpbu\\App\\Backup\\Sync\\Rsync', $sync, 'classes should match');
    }

    /**
     * Tests Factory::createCleaner
     */
    public function testCreateCleaner()
    {
        $factory = new Factory();
        $sync    = $factory->createCleaner('Capacity', ['size' => '10M']);

        $this->assertInstanceOf('phpbu\\App\\Backup\\Cleaner\\Capacity', $sync, 'classes should match');
    }

    /**
     * Tests Factory::createType
     */
    public function testCreateUnknown()
    {
        $this->expectException(Exception::class);

        $factory = new Factory();
        $factory->createSync('Unknown', ['foo' => 'bar']);

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Factory::register
     */
    public function testRegisterCheckOk()
    {
        Factory::register('check', 'dummy', '\\phpbu\\App\\Backup\\Check\\FakeCheck');

        $factory = new Factory();
        $dummy   = $factory->createCheck('dummy');

        $this->assertInstanceOf(
            'phpbu\\App\\Backup\\Check\\FakeCheck',
            $dummy,
            'Factory should create dummy object'
        );
    }

    /**
     * Tests Factory::register
     */
    public function testRegisterInvalidType()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('invalid', 'dummy', '\\phpbu\\App\\PhpbuAppFactoryTestCheck');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createAdapter
     */
    public function testCreateAdapterThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('adapter', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createAdapter('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createSource
     */
    public function testCreateSourceThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('source', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createSource('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createSource
     */
    public function testCreateCrypterThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('crypter', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createCrypter('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createLogger
     */
    public function testCreateLoggerThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('logger', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createLogger('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createLogger
     */
    public function testCreateLoggerThatIsLoggerButNoListener()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('logger', 'nothing', '\\phpbu\\App\\Log\\FakeLoggerNoListener', true);

        $factory = new Factory();
        $factory->createLogger('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createCleaner
     */
    public function testCreateCleanerThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('cleaner', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createCleaner('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createCleaner
     */
    public function testCreateSyncThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('sync', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createSync('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createCleaner
     */
    public function testCreateCheckThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('check', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createCheck('nothing');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::createRunner
     */
    public function testCreateRunnerThatIsNone()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('runner', 'nothing', '\\phpbu\\App\\Factory\\FakeNothing', true);

        $factory = new Factory();
        $factory->createRunner('nothing', false);

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::register
     */
    public function testRegisterExistingCheck()
    {
        $this->expectException('phpbu\App\Exception');
        Factory::register('check', 'sizemin', '\\phpbu\\App\\Backup\\Check\\FakeCheck');

        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Tests Factory::register
     *
     * @depends testRegisterExistingCheck
     */
    public function testRegisterExistingCheckForce()
    {
        Factory::register('check', 'sizemin', '\\phpbu\\App\\Backup\\Check\\FakeCheck', true);

        $factory = new Factory();
        $dummy   = $factory->createCheck('sizemin');

        $this->assertInstanceOf(
            'phpbu\\App\\Backup\\Check\\FakeCheck',
            $dummy,
            'Factory should create dummy object'
        );
    }
}
