<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * InfluxdumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class InfluxdumpTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Influxdump::getExecutable
     */
    public function testDefault()
    {
        $target    = $this->createTargetMock();
        $influxd = new Influxdump();
        $influxd->setup(['pathToInfluxdump' => PHPBU_TEST_BIN]);

        $executable = $influxd->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/influxd backup -portable -host=\'localhost:8088\' ', $executable->getCommand());
    }

    /**
     * Tests Influxdump::getExecutable
     */
    public function testHost()
    {
        $target    = $this->createTargetMock();
        $influxd = new Influxdump();
        $influxd->setup(['pathToInfluxdump' => PHPBU_TEST_BIN, 'host' => 'localhost:8088']);

        $executable = $influxd->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/influxd backup -portable -host=\'localhost:8088\' ', $executable->getCommand());
    }

    /**
     * Tests Influxdump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
            ->method('run')
            ->willReturn($this->getRunnerResultMock(0, 'influxd'));

        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $influxd = new Influxdump($runner);
        $influxd->setup(['pathToInfluxdump' => PHPBU_TEST_BIN]);

        $status = $influxd->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Influxdump::backup
     */
    public function testSimulate()
    {
        $runner    = $this->getRunnerMock();
        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $influxd = new Influxdump($runner);
        $influxd->setup(['pathToInfluxdump' => PHPBU_TEST_BIN]);

        $status = $influxd->simulate($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Influxdump::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $file = sys_get_temp_dir() . '/fakedump';
        file_put_contents($file, '# influxdb fake dump');

        $runnerResultMock = $this->getRunnerResultMock(1, 'influxd', '', '', $file);
        $runner           = $this->getRunnerMock();
        $runner->expects($this->once())
            ->method('run')
            ->willReturn($runnerResultMock);

        $target    = $this->createTargetMock($file);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $influxd= new Influxdump($runner);
        $influxd->setup(['pathToInfluxdump' => PHPBU_TEST_BIN]);

        try {
            $influxd->backup($target, $appResult);
        } catch (\Exception $e) {
            $this->assertFileNotExists($file);
            throw $e;
        }
    }
}
