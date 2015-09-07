<?php
namespace phpbu\App\Cli\Executable;

/**
 * ArangodumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class ArangodumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Arangodump::createProcess
     */
    public function testDefault()
    {
        $expected = 'arangodump --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoDumpDir()
    {
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->getCommandLine();
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testShowStdErr()
    {
        $expected = 'arangodump --output-directory \'./dump\'';
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $arango = new Arangodump($path);
        $arango->dumpTo('./dump')->showStdErr(true);

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testUser()
    {
        $expected = 'arangodump --server.username \'root\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->credentials('root')->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testPassword()
    {
        $expected = 'arangodump --server.password \'secret\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->credentials(null, 'secret')->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testEndpoint()
    {
        $expected = 'arangodump --server.endpoint \'tcp://example.com:8529\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->useEndpoint('tcp://example.com:8529')->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testDatabase()
    {
        $expected = 'arangodump --server.database \'myDatabase\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->dumpDatabase('myDatabase')->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testCollections()
    {
        $expected = 'arangodump --collection \'col1\' --collection \'col2\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->dumpCollections(array('col1', 'col2'))->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testDisableAuthentication()
    {
        $expected = 'arangodump --server.disable-authentication \'true\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->disableAuthentication(true)->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testIncludeSystemCollections()
    {
        $expected = 'arangodump --include-system-collections \'true\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->includeSystemCollections(true)->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }

    /**
     * Tests Arangodump::createProcess
     */
    public function testDumpData()
    {
        $expected = 'arangodump --dump-data \'true\' --output-directory \'./dump\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $arango   = new Arangodump($path);
        $arango->dumpData(true)->dumpTo('./dump');

        $this->assertEquals($path . '/' . $expected, $arango->getCommandLine());
    }
}
