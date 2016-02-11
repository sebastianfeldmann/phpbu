<?php
namespace phpbu\App\Cli\Executable;

/**
 * Mongodump Executable Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class MongodumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Mongodump::createProcess
     */
    public function testDefault()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump');

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFailNoDumpDir()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->getCommandLine();
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testUser()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->credentials('root');

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --user \'root\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testPassword()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->credentials(null, 'secret');

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --password \'secret\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testHost()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->useHost('example.com');

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --host \'example.com\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testDatabases()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->dumpDatabases(array('db1', 'db2'));

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --db \'db1\' --db \'db2\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testCollections()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->dumpCollections(array('collection1', 'collection2'));

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --collection \'collection1\' --collection \'collection2\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testIPv6()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->useIpv6(true);

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --ipv6', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testExcludeCollections()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->excludeCollections(array('col1', 'col2'));

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --excludeCollection \'col1\' \'col2\'', $mongo->getCommandLine());
    }

    /**
     * Tests Mongodump::createProcess
     */
    public function testExcludeCollectionsWithPrefix()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $mongo = new Mongodump($path);
        $mongo->dumpToDirectory('./dump')->excludeCollectionsWithPrefix(array('pre1', 'pre2'));

        $this->assertEquals($path . '/mongodump --out \'./dump' . '\' --excludeCollectionWithPrefix \'pre1\' \'pre2\'', $mongo->getCommandLine());
    }
}
