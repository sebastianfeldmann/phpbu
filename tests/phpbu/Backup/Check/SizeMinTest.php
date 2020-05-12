<?php
namespace phpbu\App\Backup\Check;

use PHPUnit\Framework\TestCase;

/**
 * SizeMinTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeMinTest extends TestCase
{
    /**
     * Tests SizeMin::pass
     */
    public function testPass()
    {
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->method('getSize')->willReturn(1030);

        $check = new SizeMin();

        $this->assertTrue(
            $check->pass($targetStub, '500B', $collectorStub, $resultStub),
            'size of stub should be greater 500'
        );
        $this->assertTrue(
            $check->pass($targetStub, '1k', $collectorStub, $resultStub),
            'size of stub should be greater 1024B'
        );
        $this->assertFalse(
            $check->pass($targetStub, '2k', $collectorStub, $resultStub),
            'size of stub should be smaller 2048'
        );
    }

    /**
     * Tests SiezeMin::simulate
     */
    public function testSimulate()
    {
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())->method('debug');
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $check = new SizeMin();
        $check->simulate($targetStub, '5000', $collectorStub, $resultStub);
    }
}
