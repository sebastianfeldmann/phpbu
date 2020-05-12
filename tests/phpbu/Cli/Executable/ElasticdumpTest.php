<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

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
class ElasticdumpTest extends TestCase
{
    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testDefault()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/\' --output=\'./foo.json\'';
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200')->dumpTo('./foo.json');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $elastic->getCommand());
    }

    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testUser()
    {
        $expected = 'elasticdump --input=\'http://root@localhost:9200/\' --output=\'./foo.json\'';
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200')->dumpTo('./foo.json')->credentials('root');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $elastic->getCommand());
    }

    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testUserPassword()
    {
        $expected = 'elasticdump --input=\'http://root:secret@localhost:9200/\' --output=\'./foo.json\'';
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200')->dumpTo('./foo.json')->credentials('root', 'secret');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $elastic->getCommand());
    }

    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testIndex()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/myIndex\' --output=\'./foo.json\'';
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200')->dumpIndex('myIndex')->dumpTo('./foo.json');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $elastic->getCommand());
    }

    /**
     * Tests Elasticdump::createCommand
     */
    public function testType()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/\' --type=\'mapping\' --output=\'./foo.json\'';
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200')->dumpType('mapping')->dumpTo('./foo.json');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $elastic->getCommand());
    }

    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testHostWithPath()
    {
        $expected = 'elasticdump --input=\'http://localhost:9200/foo/\' --output=\'./foo.json\'';
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200/foo')->dumpTo('./foo.json');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $elastic->getCommand());
    }

    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testNoHost()
    {
        $this->expectException('phpbu\App\Exception');
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->getCommand();
    }

    /**
     * Tests Elasticdump::createCommandLine
     */
    public function testNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $elastic  = new Elasticdump(PHPBU_TEST_BIN);
        $elastic->useHost('localhost:9200')->getCommand();
    }
}
