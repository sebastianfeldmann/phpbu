<?php
namespace phpbu\Backup\Sync;

/**
 * CopycomTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.5
 */
class CopycomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Copycom::setUp
     */
    public function testSetUpOk()
    {
        $copycom = new Copycom();
        $copycom->setup(array(
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoAppKey()
    {
        $copycom = new Copycom();
        $copycom->setup(array(
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ));
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoApSecret()
    {
        $copycom = new Copycom();
        $copycom->setup(array(
            'app.key'     => 'dummy-app-key',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ));
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpUserKey()
    {
        $copycom = new Copycom();
        $copycom->setup(array(
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.secret' => 'dummy-user-secret',
            'path'        => '/'
        ));
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoUserSecret()
    {
        $copycom = new Copycom();
        $copycom->setup(array(
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'path'        => '/'
        ));
    }

    /**
     * Tests Copycom::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $copycom = new Copycom();
        $copycom->setup(array(
            'app.key'     => 'dummy-app-key',
            'app.secret'  => 'dummy-app-secret',
            'user.key'    => 'dummy-user-key',
            'user.secret' => 'dummy-user-secret',
        ));
    }
}
