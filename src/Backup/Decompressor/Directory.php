<?php

namespace phpbu\App\Backup\Decompressor;

use phpbu\App\Backup\Target;

/**
 * Class Directory
 *
 * @package    phpbu
 * @subpackage
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class Directory implements Decompressable
{

    /**
     * Return extraction command for given filename
     *
     * @param \phpbu\App\Backup\Target $target
     * @return string
     */
    public function decompress(Target $target): string
    {
        return 'tar -xvf ' . $target->getFilename();
    }
}
