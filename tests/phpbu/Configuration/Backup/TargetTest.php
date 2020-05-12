<?php
namespace phpbu\App\Configuration\Backup;

use PHPUnit\Framework\TestCase;

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
class TargetTest extends TestCase
{
    /**
     * Tests Target::__construct()
     */
    public function testMandatoryDir()
    {
        $this->expectException('phpbu\App\Exception');
        $target = new Target('', 'bar.txt');
    }

    /**
     * Tests Target::__construct()
     */
    public function testMandatoryFile()
    {
        $this->expectException('phpbu\App\Exception');
        $target = new Target('/foo', '');
    }
}
