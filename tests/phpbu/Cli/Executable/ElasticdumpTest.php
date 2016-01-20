<?php
namespace phpbu\App\Cli\Executable;

/**
 * Elasticdump Executable Test
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
class ElasticdumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Elasticdump::createProcess
     */
    public function testDefault()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/\' --output=\'./foo.json\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200')->dumpTo('./foo.json');

        $this->assertEquals($path . '/' . $expected, $elastic->getCommandLine());
    }

    /**
     * Tests Elasticdump::createProcess
     */
    public function testUser()
    {
        $expected = 'elasticdump --input=\'http://root@localhost:9200/\' --output=\'./foo.json\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200')->dumpTo('./foo.json')->credentials('root');

        $this->assertEquals($path . '/' . $expected, $elastic->getCommandLine());
    }

    /**
     * Tests Elasticdump::createProcess
     */
    public function testUserPassword()
    {
        $expected = 'elasticdump --input=\'http://root:secret@localhost:9200/\' --output=\'./foo.json\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200')->dumpTo('./foo.json')->credentials('root', 'secret');

        $this->assertEquals($path . '/' . $expected, $elastic->getCommandLine());
    }

    /**
     * Tests Elasticdump::createProcess
     */
    public function testIndex()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/myIndex\' --output=\'./foo.json\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200')->dumpIndex('myIndex')->dumpTo('./foo.json');

        $this->assertEquals($path . '/' . $expected, $elastic->getCommandLine());
    }

    /**
     * Tests Elasticdump::createProcess
     */
    public function testType()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/\' --type=\'mapping\' --output=\'./foo.json\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200')->dumpType('mapping')->dumpTo('./foo.json');

        $this->assertEquals($path . '/' . $expected, $elastic->getCommandLine());
    }

    /**
     * Tests Elasticdump::createProcess
     */
    public function testHostWithPath()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/foo/\' --output=\'./foo.json\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200/foo')->dumpTo('./foo.json');

        $this->assertEquals($path . '/' . $expected, $elastic->getCommandLine());
    }

    /**
     * Tests Elasticdump::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoHost()
    {
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->getCommandLine();
    }

    /**
     * Tests Elasticdump::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoTarget()
    {
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $elastic  = new Elasticdump($path);
        $elastic->useHost('localhost:9200')->getCommandLine();
    }
}
