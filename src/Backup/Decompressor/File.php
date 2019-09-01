<?php declare(strict_types=1);

namespace phpbu\App\Backup\Decompressor;

use phpbu\App\Backup\Target;

/**
 * Class File
 *
 * @package    phpbu
 * @subpackage
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class File implements Decompressable
{
    /**
     * Return extraction command for given filename
     *
     * @param \phpbu\App\Backup\Target $target
     * @return string
     */
    public function decompress(Target $target): string
    {
        $compression = $target->getCompression();

        if ($compression->getCommand() === 'zip') {
            return 'unzip ' . $target->getFilename();
        }

        return $compression->getCommand() . ' -dk ' . $target->getFilename();
    }
}
