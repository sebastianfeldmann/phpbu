<?php
namespace phpbu\App\Cli;

/**
 * StatisticsTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.2
 */
class StatisticsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Statistics::resourceUsage
     */
    public function testResourceUsage()
    {
        $usage = Statistics::resourceUsage();

        $this->assertTrue(strpos($usage, 'Time:') !== false);
        $this->assertTrue(strpos($usage, 'Memory:') !== false);
    }
}
