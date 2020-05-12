<?php
namespace phpbu\App\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * Backup Configuration test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class BackupTest extends TestCase
{
    /**
     * Tests Backup::__construct()
     */
    public function testInitState()
    {
        $backup = new Backup('name', false);

        $this->assertEquals('name', $backup->getName());
        $this->assertIsArray($backup->getChecks());
        $this->assertIsArray($backup->getSyncs());
    }

    /**
     * Tests Backup::__construct()
     */
    public function testAddCheck()
    {
        $backup = new Backup('name', false);
        $check  = new Backup\Check('SizeMin', '10M');

        $this->assertCount(0, $backup->getChecks());
        $backup->addCheck($check);
        $this->assertCount(1, $backup->getChecks());
    }

    /**
     * Tests Backup::getName()
     *
     */
    public function testGetNameException()
    {
        $this->expectException('phpbu\App\Exception');
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

        $this->assertCount(0, $backup->getSyncs());
        $backup->addSync($sync);
        $this->assertCount(1, $backup->getSyncs());
    }

    /**
     * Tests Backup::hasCrypt()
     */
    public function testHasCrypt()
    {
        $backup = new Backup('name', false);
        $crypt  = new Backup\Crypt('openssl', true);

        $backup->setCrypt($crypt);
        $this->assertEquals($crypt, $backup->getCrypt());
        $this->assertTrue($backup->hasCrypt());
    }

    /**
     * Tests Backup::hasCleanup()
     */
    public function testHasCleanup()
    {
        $backup  = new Backup('name', false);
        $cleanup = new Backup\Cleanup('capacity', true, []);

        $backup->setCleanup($cleanup);
        $this->assertEquals($cleanup, $backup->getCleanup());
        $this->assertTrue($backup->hasCleanup());
    }
}
