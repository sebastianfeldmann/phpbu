<?php
namespace phpbu\Backup\Check;

/**
 * SizeDiffPreviousPercentTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
        $collectorStub = $this->getMockBuilder('\\phpbu\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\Backup\\Target')
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
     * Create a list of splFileInfo stubs
     *
     * @param  integer $size      Size in byte the stubs will return on getSize()
     * @param  integer $amount    Amount of stubs in list
     * @return array<splFileInfo>
     */
    protected function getFileListMock($size, $amount = 5)
    {
        $list = array();
        for ($i = 0; $i < $amount; $i++) {
            $fileStub = $this->getMockBuilder('\\phpbu\\Backup\\File')
                         ->disableOriginalConstructor()
                         ->getMock();
            $fileStub->method('getSize')->willReturn($size);
            $list['201401' . str_pad($i, 2, '0', STR_PAD_LEFT)] = $fileStub;
        }
        return $list;
    }
}
