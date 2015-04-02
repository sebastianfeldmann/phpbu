<?php
namespace phpbu\App\Event\Backup;

use phpbu\App\Configuration;

/**
 * Backup Start test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class StartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Start::getConfiguration
     */
    public function testGetConfiguration()
    {
        $config = new Configuration\Backup('dummy', false);
        $start  = new Start($config);
        $this->assertEquals($config, $start->getConfiguration());
    }
}
