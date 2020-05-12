<?php
namespace phpbu\App\Cli;

use PHPUnit\Framework\TestCase;

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
class StatisticsTest extends TestCase
{
    /**
     * Tests Statistics::resourceUsage
     */
    public function testResourceUsage()
    {
        $usage = Statistics::resourceUsage();

        $this->assertStringContainsString('Time:', $usage);
        $this->assertStringContainsString('Memory:', $usage);
    }
}
