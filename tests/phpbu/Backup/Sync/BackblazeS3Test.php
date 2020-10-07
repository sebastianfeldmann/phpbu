<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * BackblazeS3Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Vladimir Konchakovsaky <vk@etradeua.com>
 * @copyright  Vladimir Konchakovsaky <vk@etradeua.com>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 */
class BackblazeS3Test extends TestCase
{

    use BaseMockery;

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpOk()
    {
        $amazonS3 = new BackblazeS3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

}
