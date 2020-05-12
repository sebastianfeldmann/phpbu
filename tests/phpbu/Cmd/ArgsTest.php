<?php
namespace phpbu\App\Cmd;

use PHPUnit\Framework\TestCase;

/**
 * Args parser test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class ArgsTest extends TestCase
{
    /**
     * Test short option -h
     */
    public function testGetOptionsShortH()
    {
        $args    = new Args();
        $options = $args->getOptions(['-h']);
        $this->assertTrue($options['-h'], 'short option -h must be set');
    }

    /**
     * Test self-update
     */
    public function testGetSelfUpdate()
    {
        $args    = new Args(true);
        $options = $args->getOptions(['--self-update']);
        $this->assertTrue($options['--self-update'], 'long option --self-update must be set');
    }

    /**
     * Test short option -x
     */
    public function testGetOptionsFail()
    {
        $this->expectException('phpbu\App\Exception');
        $args    = new Args();
        $options = $args->getOptions(['-x']);
        $this->assertFalse(true, 'short option x is invalid');
    }

    /**
     * Test short option -V
     */
    public function testGetOptionsShortUpperV()
    {
        $args    = new Args();
        $options = $args->getOptions(['-V', 'foo', 'bar']);
        $this->assertTrue($options['-V'], 'short option -V must be set');
    }

    /**
     * Test short option -v
     */
    public function testGetOptionsShortLowerV()
    {
        $args    = new Args();
        $options = $args->getOptions(['-v', 'foo', 'bar']);
        $this->assertTrue($options['-v'], 'short option -v must be set');
    }

    /**
     * Test long option --version
     */
    public function testGetOptionsLongVersion()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', 'bar', '--version']);
        $this->assertTrue($options['--version'], 'long option --version must be set');
    }

    /**
     * Test log option --help
     */
    public function testGetOptionsLongHelp()
    {
        $args    = new Args();
        $options = $args->getOptions(['--help', 'foo', 'bar']);
        $this->assertTrue($options['--help'], 'long option --help must be set');
    }

    /**
     * Test log option --restore
     */
    public function testGetOptionsLongRestore()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', '--restore', 'bar']);
        $this->assertTrue($options['--restore'], 'long option --restore must be set');
    }

    /**
     * Test log option --simulate
     */
    public function testGetOptionsLongSimulate()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', '--simulate', 'bar']);
        $this->assertTrue($options['--simulate'], 'long option --simulate must be set');
    }

    /**
     * Test log option --verbose
     */
    public function testGetOptionsLongVerbose()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', '--verbose', 'bar']);
        $this->assertTrue($options['--verbose'], 'long option --verbose must be set');
    }

    /**
     * Test log option --bootstrap
     */
    public function testGetOptionsLongBootstrap()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', '--bootstrap=backup/bootstrap.php', 'bar']);
        $this->assertEquals(
            'backup/bootstrap.php',
            $options['--bootstrap'],
            'long option --bootstrap must be set correctly'
        );
    }

    /**
     * Test log option --limit
     */
    public function testGetOptionsLongLimit()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', '--limit=foo,bar,baz', 'bar']);
        $this->assertEquals(
            'foo,bar,baz',
            $options['--limit'],
            'long option --limit must be set correctly'
        );
    }

    /**
     * Test log option --configuration
     */
    public function testGetOptionsLongConfiguration()
    {
        $args    = new Args();
        $options = $args->getOptions(['foo', '--configuration=conf/my.xml.dist', 'bar']);
        $this->assertEquals(
            'conf/my.xml.dist',
            $options['--configuration'],
            'long option --configuration must be set correctly'
        );
    }

    public function testGetOptionsLongMissingArgument()
    {
        $this->expectException('phpbu\App\Exception');
        $args    = new Args();
        $options = $args->getOptions(['--bootstrap']);
        $this->assertTrue(false, 'should not be called');
    }

    public function testGetOptionsLongUnneccesaryArgument()
    {
        $this->expectException('phpbu\App\Exception');
        $args    = new Args();
        $options = $args->getOptions(['--version=foo']);
        $this->assertTrue(false, 'should not be called');
    }

    public function testGetOptionsLongInvalidArgument()
    {
        $this->expectException('phpbu\App\Exception');
        $args    = new Args();
        $options = $args->getOptions(['--bootstrap=foo=bar']);
        $this->assertTrue(false, 'should not be called');
    }

    public function testGetOptionsLongUnknownOption()
    {
        $this->expectException('phpbu\App\Exception');
        $args    = new Args();
        $options = $args->getOptions(['--foo']);
        $this->assertTrue(false, 'should not be called');
    }
}
