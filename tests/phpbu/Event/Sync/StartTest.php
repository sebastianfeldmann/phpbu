<?php
namespace phpbu\App\Event\Sync;

use phpbu\App\Configuration;

/**
 * Sync Start test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class StartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Start::getConfiguration
     */
    public function testGetConfiguration()
    {
        $config = new Configuration\Backup\Sync('dummy', false);
        $start  = new Start($config);
        $this->assertEquals($config, $start->getConfiguration());
    }
}
