<?php
namespace phpbu\App\Backup\Check;

/**
 * SizeDiffPreviousPercentTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeDiffPreviousPercentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests SizeDiffPreviousPercent::pass
     */
    public function testPass()
    {
        $fileList      = $this->getFileListMock(1000);
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $targetStub->method('getSize')->willReturn(1090);
        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $check = new SizeDiffPreviousPercent();

        $this->assertTrue(
            $check->pass($targetStub, '10', $collectorStub, $resultStub),
            'size of stub should not differ more then 10%'
        );

        $this->assertFalse(
            $check->pass($targetStub, '5', $collectorStub, $resultStub),
            'size of stub should differ more then 5%'
        );
    }

    /**
     * Create a list of File stubs
     *
     * @param  integer $size      Size in byte the stubs will return on getSize()
     * @param  integer $amount    Amount of stubs in list
     * @return array<splFileInfo>
     */
    protected function getFileListMock($size, $amount = 5)
    {
        $list = array();
        for ($i = 0; $i < $amount; $i++) {
            $fileStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\File')
                             ->disableOriginalConstructor()
                             ->getMock();
            $fileStub->method('getSize')->willReturn($size);
            $list['201401' . str_pad($i, 2, '0', STR_PAD_LEFT)] = $fileStub;
        }
        return $list;
    }
}
