<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * LdapdumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Julian Marié <julian.marie@free.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class LdapdumpTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testDefault()
    {
        $target    = $this->createTargetMock();
        $ldap = new Ldapdump();
        $ldap->setup(['pathToLdapdump' => PHPBU_TEST_BIN]);

        $executable = $ldap->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testHost()
    {
        $target    = $this->createTargetMock();
        $ldap = new Ldapdump();
        $ldap->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'host' => 'localhost']);

        $executable = $ldap->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x -h \'localhost\' \'\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testPort()
    {
        $target    = $this->createTargetMock();
        $ldapdump = new Ldapdump();
        $ldapdump->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'port' => '389']);

        $executable = $ldapdump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x -p \'389\' \'\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testSearchBase()
    {
        $target    = $this->createTargetMock();
        $ldapdump = new Ldapdump();
        $ldapdump->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'searchBase' => 'ou=Users,dc=fr']);

        $executable = $ldapdump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -b \'ou=Users,dc=fr\' -x \'\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testBindDn()
    {
        $target    = $this->createTargetMock();
        $ldapdump = new Ldapdump();
        $ldapdump->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'bindDn' => 'cn=admin,dc=fr']);

        $executable = $ldapdump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x -D \'cn=admin,dc=fr\' \'\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testFilter()
    {
        $target    = $this->createTargetMock();
        $ldapdump = new Ldapdump();
        $ldapdump->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'filter' => '(objectclass=*)']);

        $executable = $ldapdump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'(objectclass=*)\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testOneAttrs()
    {
        $target    = $this->createTargetMock();
        $ldapdump = new Ldapdump();
        $ldapdump->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'attrs' => '*']);

        $executable = $ldapdump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'\' \'*\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::getExecutable
     */
    public function testTwoAttrs()
    {
        $target    = $this->createTargetMock();
        $ldapdump = new Ldapdump();
        $ldapdump->setup(['pathToLdapdump' => PHPBU_TEST_BIN, 'attrs' => '*,+']);

        $executable = $ldapdump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/ldapsearch -x \'\' \'*\' \'+\'', $executable->getCommand());
    }

    /**
     * Tests Ldapdump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
            ->method('run')
            ->willReturn($this->getRunnerResultMock(0, 'ldap'));

        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $ldap = new Ldapdump($runner);
        $ldap->setup(['pathToLdapdump' => PHPBU_TEST_BIN]);

        $status = $ldap->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Ldapdump::backup
     */
    public function testSimulate()
    {
        $runner    = $this->getRunnerMock();
        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $ldap = new Ldapdump($runner);
        $ldap->setup(['pathToLdapdump' => PHPBU_TEST_BIN]);

        $status = $ldap->simulate($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Ldapdump::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $file = sys_get_temp_dir() . '/fakedump';
        file_put_contents($file, '# ldapb fake dump');

        $runnerResultMock = $this->getRunnerResultMock(1, 'ldap', '', '', $file);
        $runner           = $this->getRunnerMock();
        $runner->expects($this->once())
            ->method('run')
            ->willReturn($runnerResultMock);

        $target    = $this->createTargetMock($file);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $ldap= new Ldapdump($runner);
        $ldap->setup(['pathToLdapdump' => PHPBU_TEST_BIN]);

        try {
            $ldap->backup($target, $appResult);
        } catch (\Exception $e) {
            $this->assertFileNotExists($file);
            throw $e;
        }
    }
}
