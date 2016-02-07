<?php
namespace phpbu\App\Cli\Executable;

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
class PgdumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Pgdump::getCommandLine
     */
    public function testDefault()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testWithUserAndPassword()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $user   = 'mrx';
        $pass   = 'secret';
        $pgdump = new Pgdump($path);
        $pgdump->credentials($user, $pass)
               ->dumpTo($file);

        $this->assertEquals(
            'PGPASSWORD=\'secret\' ' . $path . '/pg_dump -w --username=\'mrx\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testHostAndPort()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->useHost('localhost')->usePort('1234')->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --host=\'localhost\' --port=\'1234\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testDatabase()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testSchemaOnly()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpSchemaOnly(true)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --schema-only --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );

        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpSchemaOnly(false)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSchemaOnlyAfterDataOnly()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')
               ->dumpDataOnly(true)
               ->dumpSchemaOnly(true)
               ->dumpTo($file);
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testDataOnly()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpDataOnly(true)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --data-only --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );

        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpDataOnly(false)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testDataOnlyAfterSchemaOnly()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')
               ->dumpSchemaOnly(true)
               ->dumpDataOnly(true)
               ->dumpTo($file);
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testSchemasToDump()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpSchemas(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --schema=\'fiz\' --schema=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testExcludeSchemas()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->excludeSchemas(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --exclude-schema=\'fiz\' --exclude-schema=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testTablesToDump()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpTables(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --table=\'fiz\' --table=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testExcludeTables()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->excludeTables(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --exclude-table=\'fiz\' --exclude-table=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testExcludeTableData()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->excludeTableData(['fiz', 'baz'])->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --exclude-table-data=\'fiz\' --exclude-table-data=\'baz\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testEncoding()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->encode('utf-8')->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --encoding=\'utf-8\' --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testFormat()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpFormat('c')->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --file=\'/tmp/foo\' --format=\'c\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidFormat()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpFormat('fail')->dumpTo($file);
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testAddDropStatements()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->addDropStatements(true)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --clean --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testSkipOwner()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->skipOwnerCommands(true)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --no-owner --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testNoTablespaces()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpNoTablespaces(true)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --no-tablespaces --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }

    /**
     * Tests Pgdump::getCommandLine
     */
    public function testNoPrivileges()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $file   = '/tmp/foo';
        $pgdump = new Pgdump($path);
        $pgdump->dumpDatabase('phpbu')->dumpNoPrivileges(true)->dumpTo($file);

        $this->assertEquals(
            $path . '/pg_dump -w --dbname=\'phpbu\' --no-acl --file=\'/tmp/foo\'',
            $pgdump->getCommandLine()
        );
    }
}
