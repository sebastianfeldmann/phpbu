<?php
namespace phpbu\App\Event\Backup;

use phpbu\App\Backup\Source\FakeSource;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Backup Start test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class StartTest extends TestCase
{
    /**
     * Tests Start::getConfiguration
     */
    public function testGetConfiguration()
    {
        $config = new Configuration\Backup('dummy', false);
        $target = new Target(sys_get_temp_dir() . '/test', 'targetFile');
        $source = new FakeSource();
        $start  = new Start($config, $target, $source);
        $this->assertEquals($config, $start->getConfiguration());
    }
}
