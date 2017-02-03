<?php
namespace phpbu\App\Configuration\Backup;

/**
 * Target Configuration test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class TargetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Target::__construct()
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testMandatoryDir()
    {
        $target = new Target('', 'bar.txt');
    }

    /**
     * Tests Target::__construct()
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testMandatoryFile()
    {
        $target = new Target('/foo', '');
    }
}
