<?php
namespace phpbu\App\Event\App;

use phpbu\App\Result;
use PHPUnit\Framework\TestCase;

/**
 * End test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class EndTest extends TestCase
{
    /**
     * Tests End::getResult
     */
    public function testGetResult()
    {
        $r = $this->createMock(Result::class);

        $end = new End($r);

        $this->assertEquals($r, $end->getResult());
    }
}
