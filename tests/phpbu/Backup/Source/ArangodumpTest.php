<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * Arangodump Source Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class ArangodumpTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Arangodump::getExecutable
     */
    public function testDefault()
    {
        $target     = $this->createTargetMock('./dir/foo.dump');
        $arangodump = new Arangodump();
        $arangodump->setup(['pathToArangodump' => PHPBU_TEST_BIN]);

        $executable = $arangodump->getExecutable($target);
        $expected   = PHPBU_TEST_BIN . '/arangodump --output-directory \'./dir/dump\'';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests Arangodump::getExecutable
     */
    public function testUser()
    {
        $target     = $this->createTargetMock('./dir/foo.dump');
        $arangodump = new Arangodump();
        $arangodump->setup(['pathToArangodump' => PHPBU_TEST_BIN, 'username' => 'root']);

        $executable = $arangodump->getExecutable($target);
        $expected   = PHPBU_TEST_BIN . '/arangodump --server.username \'root\' --output-directory \'./dir/dump\'';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests Arangodump::Executable
     */
    public function testCollections()
    {
        $target     = $this->createTargetMock('./dir/foo.dump');
        $arangodump = new Arangodump();
        $arangodump->setup(['pathToArangodump' => PHPBU_TEST_BIN, 'collections' => 'collection1,collection2']);

        $executable = $arangodump->getExecutable($target);
        $expected   = PHPBU_TEST_BIN . '/arangodump --collection \'collection1\' '
                    . '--collection \'collection2\' --output-directory \'./dir/dump\'';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests Arangodump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'arangodump'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $arangodump = new Arangodump($runner);
        $arangodump->setup(['pathToArangodump' => PHPBU_TEST_BIN]);

        $status = $arangodump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Arangodump::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'arangodump'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $arangodump = new Arangodump($runner);
        $arangodump->setup(['pathToArangodump' => PHPBU_TEST_BIN]);
        $arangodump->backup($target, $appResult);
    }
}
