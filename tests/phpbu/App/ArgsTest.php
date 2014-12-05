<?php
namespace phpbu\App;

/**
 * Args parser test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class ArgsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test short option -h
     */
    public function testGetOptionsShortH()
    {
        $args    = new Args();
        $options = $args->getOptions(array('-h'));
        $this->assertTrue($options['-h'], 'short option -h must be set');
    }

    /**
     * Test short option -V
     */
    public function testGetOptionsShortUpperV()
    {
        $args    = new Args();
        $options = $args->getOptions(array('-V', 'foo', 'bar'));
        $this->assertTrue($options['-V'], 'short option -V must be set');
    }

    /**
     * Test short option -v
     */
    public function testGetOptionsShortLowerV()
    {
        $args    = new Args();
        $options = $args->getOptions(array('-v', 'foo', 'bar'));
        $this->assertTrue($options['-v'], 'short option -v must be set');
    }

    /**
     * Test long option --version
     */
    public function testGetOptionsLongVersion()
    {
        $args    = new Args();
        $options = $args->getOptions(array('foo', 'bar', '--version'));
        $this->assertTrue($options['--version'], 'long option --version must be set');
    }

    /**
     * Test log option --help
     */
    public function testGetOptionsLongHelp()
    {
        $args    = new Args();
        $options = $args->getOptions(array('--help', 'foo', 'bar'));
        $this->assertTrue($options['--help'], 'long option --help must be set');
    }

    /**
     * Test log option --verbose
     */
    public function testGetOptionsLongVerbose()
    {
        $args    = new Args();
        $options = $args->getOptions(array('foo', '--verbose', 'bar'));
        $this->assertTrue($options['--verbose'], 'long option --verbose must be set');
    }

    /**
     * Test log option --include-path
     */
    public function testGetOptionsLongIncludePath()
    {
        $args    = new Args();
        $options = $args->getOptions(array('foo', '--include-path=/foo/bar', 'bar'));
        $this->assertEquals(
            '/foo/bar',
            $options['--include-path'],
            'long option --include-path must be set correctly'
        );
    }

    /**
     * Test log option --bootstrap
     */
    public function testGetOptionsLongBootstrap()
    {
        $args    = new Args();
        $options = $args->getOptions(array('foo', '--bootstrap=backup/bootstrap.php', 'bar'));
        $this->assertEquals(
            'backup/bootstrap.php',
            $options['--bootstrap'],
            'long option --bootstrap must be set correctly'
        );
    }

    /**
     * Test log option --configuration
     */
    public function testGetOptionsLongConfiguration()
    {
        $args    = new Args();
        $options = $args->getOptions(array('foo', '--configuration=conf/my.xml.dist', 'bar'));
        $this->assertEquals(
            'conf/my.xml.dist',
            $options['--configuration'],
            'long option --configuration must be set correctly'
        );
    }

    /**
     * @expectedException phpbu\App\Exception
     */
    public function testGetOptionsLongMissingArgument()
    {
        $args    = new Args();
        $options = $args->getOptions(array('--bootstrap'));
        $this->assertTrue(false, 'should not be called');
    }

    /**
     * @expectedException phpbu\App\Exception
     */
    public function testGetOptionsLongUnneccesaryArgument()
    {
        $args    = new Args();
        $options = $args->getOptions(array('--version=foo'));
        $this->assertTrue(false, 'should not be called');
    }

    /**
     * @expectedException phpbu\App\Exception
     */
    public function testGetOptionsLongInvalidArgument()
    {
        $args    = new Args();
        $options = $args->getOptions(array('--bootstrap=foo=bar'));
        $this->assertTrue(false, 'should not be called');
    }

    /**
     * @expectedException phpbu\App\Exception
     */
    public function testGetOptionsLongUnknownOption()
    {
        $args    = new Args();
        $options = $args->getOptions(array('--foo'));
        $this->assertTrue(false, 'should not be called');
    }
}
