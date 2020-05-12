<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Backup\Target\Compression;
use PHPUnit\Framework\TestCase;

/**
 * LdapdumpTest ExecutableTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Julian Marié <julian.marie@free.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class LdapdumpTest extends TestCase
{
    /**
     * Tests Ldapdump::getCommand
     */
    public function testDefault()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $cmd       = $ldap->getCommand();
        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'\'', $cmd);
    }

    /**
     * Tests Ldapdump::getCommandPrintable
     */
    public function testDefaultPrintable()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $cmd       = $ldap->getCommandPrintable();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'\'', $cmd);
    }

    /**
     * Tests Ldapdump::useHost
     */
    public function testUseHost()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $ldap->useHost('localhost');
        $cmd       = $ldap->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x -h \'localhost\' \'\'', $cmd);
    }

    /**
     * Tests Ldapdump::usePort
     */
    public function testUsePort()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $ldap->usePort(389);
        $cmd       = $ldap->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x -p \'389\' \'\'', $cmd);
    }

    /**
     * Tests Ldapdump::usePort
     */
    public function testUseSearchBase()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $ldap->useSearchBase('ou=Users,dc=fr');
        $cmd       = $ldap->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -b \'ou=Users,dc=fr\' -x \'\'', $cmd);
    }

    /**
     * Tests Ldapdump::getCommand
     */
    public function testPassword()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $ldap->credentials('cn=admin,dc=fr', 'test');
        $cmd       = $ldap->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x -D \'cn=admin,dc=fr\' -w \'test\' \'\'', $cmd);
    }

    /**
     * Tests Ldapdump::useFilter
     */
    public function testUseFilter()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $ldap->useFilter('(objectclass=*)');
        $cmd       = $ldap->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'(objectclass=*)\'', $cmd);
    }

    /**
     * Tests Ldapdump::useAttributes
     */
    public function testUseAttributes()
    {
        $ldap = new Ldapdump(PHPBU_TEST_BIN);
        $ldap->useAttributes(['*', '+']);
        $cmd       = $ldap->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'\' \'*\' \'+\'', $cmd);
    }

    /**
     * Tests Ldapdump::getCommand
     */
    public function testCompressor()
    {
        $path        = realpath(__DIR__ . '/../../../_files/bin');
        $compression = Compression\Factory::create($path . '/gzip');
        $ldapdump   = new Ldapdump($path);
        $ldapdump->compressOutput($compression)->dumpTo('/tmp/foo.ldap');

        $this->assertEquals(
            $path . '/ldapsearch -x \'\' | ' . $path . '/gzip > /tmp/foo.ldap.gz',
            $ldapdump->getCommand()
        );
    }
}
