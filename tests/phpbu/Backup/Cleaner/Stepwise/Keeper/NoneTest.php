<?php
namespace phpbu\App\Backup\Cleaner\Stepwise\Keeper;

use phpbu\App\Backup\File\Local;
use PHPUnit\Framework\TestCase;

/**
 * All test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class NoneTest extends TestCase
{
    /**
     * Tests None::keep
     */
    public function testKeep()
    {
        $file = $this->createMock(Local::class);

        $keeper = new None();
        $this->assertFalse($keeper->keep($file));
    }
}
