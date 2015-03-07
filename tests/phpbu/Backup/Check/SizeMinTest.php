<?php
namespace phpbu\App\Backup\Check;

/**
 * SizeMinTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeMinTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests SizeMin::pass
     */
    public function testPass()
    {
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();
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
}
