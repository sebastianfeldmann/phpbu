<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * Pgdump Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class PgdumpTest extends TestCase
{
    /**
     * Tests Pgdump::getCommand
     */
    public function testDefault()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testWithUserAndPassword()
    {
        $file   = '/tmp/foo';
        $user   = 'mrx';
        $pass   = 'secret';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->credentials($user, $pass)
               ->dumpTo($file);

        $this->assertEquals(
            'PGPASSWORD=\'secret\' ' . PHPBU_TEST_BIN . '/pg_dump -w --username=\'mrx\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testHostAndPort()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->useHost('localhost')->usePort('1234')->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --host=\'localhost\' --port=\'1234\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testDatabase()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testSchemaOnly()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpSchemaOnly(true)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --schema-only --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );

        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpSchemaOnly(false)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testSchemaOnlyAfterDataOnly()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')
               ->dumpDataOnly(true)
               ->dumpSchemaOnly(true)
               ->dumpTo($file);
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testDataOnly()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpDataOnly(true)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --data-only --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );

        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpDataOnly(false)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testDataOnlyAfterSchemaOnly()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')
               ->dumpSchemaOnly(true)
               ->dumpDataOnly(true)
               ->dumpTo($file);
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testSchemasToDump()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpSchemas(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --schema=\'fiz\' --schema=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testExcludeSchemas()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->excludeSchemas(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN
            . '/pg_dump -w --dbname=\'phpbu\' --exclude-schema=\'fiz\' '
            . '--exclude-schema=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testTablesToDump()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpTables(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN
            . '/pg_dump -w --dbname=\'phpbu\' --table=\'fiz\' '
            . '--table=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testExcludeTables()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->excludeTables(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN
            . '/pg_dump -w --dbname=\'phpbu\' --exclude-table=\'fiz\' '
            . '--exclude-table=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testExcludeTableData()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->excludeTableData(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN
            . '/pg_dump -w --dbname=\'phpbu\' --exclude-table-data=\'fiz\' '
            . '--exclude-table-data=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testEncoding()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->encode('utf-8')->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --encoding=\'utf-8\' --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testFormat()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpFormat('c')->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\' --format=\'c\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testInvalidFormat()
    {
        $this->expectException('phpbu\App\Exception');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpFormat('fail')->dumpTo($file);
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testAddDropStatements()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->addDropStatements(true)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --clean --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testSkipOwner()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->skipOwnerCommands(true)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --no-owner --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testNoTablespaces()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpNoTablespaces(true)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --no-tablespaces --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }

    /**
     * Tests Pgdump::getCommand
     */
    public function testNoPrivileges()
    {
        $file   = '/tmp/foo';
        $pgdump = new Pgdump(PHPBU_TEST_BIN);
        $pgdump->dumpDatabase('phpbu')->dumpNoPrivileges(true)->dumpTo($file);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/pg_dump -w --dbname=\'phpbu\' --no-acl --file=\'/tmp/foo\'',
            $pgdump->getCommand()
        );
    }
}
