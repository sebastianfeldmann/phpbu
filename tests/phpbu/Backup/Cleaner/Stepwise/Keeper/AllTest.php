<?php
namespace phpbu\App\Backup\Cleaner\Stepwise\Keeper;

use PHPUnit\Framework\TestCase;

/**
 * All test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class AllTest extends TestCase
{
    /**
     * Tests All::keep
     */
    public function testKeep()
    {
        $file = $this->createMock(\phpbu\App\Backup\File\Local::class);

        $keeper = new All();
        $this->assertTrue($keeper->keep($file));
    }
}
