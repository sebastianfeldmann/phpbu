<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * MongodumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class MongodumpTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Mongodump::getExecutable
     */
    public function testDefault()
    {
        $target    = $this->createTargetMock(__FILE__);
        $mongodump = new Mongodump();
        $mongodump->setup(['pathToMongodump' => PHPBU_TEST_BIN]);

        $executable = $mongodump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mongodump --out \'' . __DIR__ . '/dump\'', $executable->getCommand());
    }

    /**
     * Tests Mongodump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'mongodump'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mongodump = new Mongodump($runner);
        $mongodump->setup(['pathToMongodump' => PHPBU_TEST_BIN]);
        $status = $mongodump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Mongodump::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'mongodump'));

        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mongodump = new Mongodump($runner);
        $mongodump->setup(['pathToMongodump' => PHPBU_TEST_BIN]);
        $mongodump->backup($target, $appResult);
    }
}
