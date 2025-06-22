<?php
namespace phpbu\App\Event\Check;

use phpbu\App\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Check Start test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class StartTest extends TestCase
{
    /**
     * Tests Start::getConfiguration
     */
    public function testGetConfiguration()
    {
        $config = new Configuration\Backup\Check('dummy', 'value');
        $start  = new Start($config);
        $this->assertEquals($config, $start->getConfiguration());
    }
}
