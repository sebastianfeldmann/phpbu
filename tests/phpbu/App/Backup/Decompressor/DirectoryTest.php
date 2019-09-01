<?php

namespace phpbu\App\Backup\Decompressor;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * Directory test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class DirectoryTest extends TestCase
{
    use BaseMockery;

    /**
     * Tests Directory::decompress
     */
    public function testDecompress()
    {
        $target  = $this->createTargetMock('foo.tar', 'foo.tar');
        $dir     = new Directory();
        $command = $dir->decompress($target);

        $this->assertEquals('tar -xvf foo.tar', $command);
    }
}
