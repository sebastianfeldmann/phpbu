<?php
namespace phpbu\App\Backup\Sync;

/**
 * CopycomTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class CopycomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Copycom::setUp
     */
    public function testSetUpOk()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Copycom::simulate
     */
    public function testSimulate()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ]);

        $resultStub = $this->getMockBuilder('\\phpbu\\App\\Result')
                           ->getMock();
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                           ->disableOriginalConstructor()
                           ->getMock();

        $copycom->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoAppKey()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ]);
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoApSecret()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.key'     => 'dummy-app-key',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ]);
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpUserKey()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ]);
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoUserSecret()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'path'        => '/'
        ]);
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $copycom = new Copycom();
        $copycom->setup([
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
        ]);
    }
}
