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
 * @link       https://phpbu.de/
 */
class BackblazeS3Test extends TestCase
{

    use BaseMockery;

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpOk()
    {
        $b2 = new BackblazeS3();
        $b2->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    public function testEndpoint()
    {
        $b2 = new BackblazeS3();
        $b2->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $createEndpointPrivateMethod = function () {
            return $this->createEndpoint();
        };

        $createEndpointClosure = $createEndpointPrivateMethod->bindTo($b2, $b2);

        $this->assertEquals($createEndpointClosure(), 'https://s3.dummy-region.backblazeb2.com');
    }
}
