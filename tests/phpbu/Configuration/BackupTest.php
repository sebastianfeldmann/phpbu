<?php
namespace phpbu\App\Configuration;

/**
 * Backup Configuration test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class BackupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Backup::__construct()
     */
    public function testInitState()
    {
        $backup = new Backup('name', false);

        $this->assertEquals('name', $backup->getName());
        $this->assertTrue(is_array($backup->getChecks()));
        $this->assertTrue(is_array($backup->getSyncs()));
    }

    /**
     * Tests Backup::__construct()
     */
    public function testAddCheck()
    {
        $backup = new Backup('name', false);
        $check  = new Backup\Check('SizeMin', '10M');

        $this->assertEquals(0, count($backup->getChecks()));
        $backup->addCheck($check);
        $this->assertEquals(1, count($backup->getChecks()));
    }

    /**
     * Tests Backup::getName()
     *
     * @expectedException \phpbu\App\Exception
     *
     */
    public function testGetNameException()
    {
        $backup = new Backup('', false);
        $backup->getName();
    }

    /**
     * Tests Backup::getName()
     */
    public function testGetNameSet()
    {
        $backup = new Backup('name', false);
        $this->assertEquals('name', $backup->getName());
    }

    /**
     * Tests Backup::getName()
     */
    public function testGetNameFromSource()
    {
        $backup = new Backup('', false);
        $backup->setSource(new Backup\Source('mysqldump'));
        $this->assertEquals('mysqldump', $backup->getName());
    }

    /**
     * Tests Backup::addSync()
     */
    public function testAddSync()
    {
        $backup = new Backup('name', false);
        $sync   = new Backup\Sync('dropbox', true);

        $this->assertEquals(0, count($backup->getSyncs()));
        $backup->addSync($sync);
        $this->assertEquals(1, count($backup->getSyncs()));
    }
}
