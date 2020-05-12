<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * Elasticdump Source Test
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
class ElasticdumpTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Elasticdump::getExecutable
     */
    public function testDefault()
    {
        $target      = $this->createTargetMock('backup.json');
        $elasticdump = new Elasticdump();
        $elasticdump->setup(['pathToElasticdump' => PHPBU_TEST_BIN]);

        $executable = $elasticdump->getExecutable($target);
        $expected   = 'elasticdump --input=\'http://localhost:9200/\' --output=\'backup.json\'';

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $executable->getCommand());
    }

    /**
     * Tests Elasticdump::getExecutable
     */
    public function testUser()
    {
        $target      = $this->createTargetMock('backup.json');
        $elasticdump = new Elasticdump();
        $elasticdump->setup(['pathToElasticdump' => PHPBU_TEST_BIN, 'user' => 'root']);

        $executable = $elasticdump->getExecutable($target);
        $expected   = 'elasticdump --input=\'http://root@localhost:9200/\' --output=\'backup.json\'';

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $executable->getCommand());
    }

    /**
     * Tests Elasticdump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'elasticdump'));

        $target    = $this->createTargetMock('backup.json');
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $elasticdump = new Elasticdump($runner);
        $elasticdump->setup(['pathToElasticdump' => PHPBU_TEST_BIN]);

        $status = $elasticdump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Elasticdump::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'elasticdump'));

        $target    = $this->createTargetMock('backup.json');
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $elasticdump = new Elasticdump($runner);
        $elasticdump->setup(['pathToElasticdump' => PHPBU_TEST_BIN]);
        $elasticdump->backup($target, $appResult);
    }
}
