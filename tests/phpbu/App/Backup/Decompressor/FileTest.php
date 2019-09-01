<?php

namespace phpbu\App\Backup\Decompressor;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * File test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class FileTest extends TestCase
{
    use BaseMockery;

    /**
     * Tests Directory::decompress
     */
    public function testDecompress()
    {
        $target  = $this->createTargetMock('foo.gz', 'foo.gz');
        $dir     = new File();
        $command = $dir->decompress($target);

        $this->assertEquals('gzip -dk foo.gz', $command);
    }
}
