<?php declare(strict_types=1);

namespace phpbu\App\Backup\Decompressor;

use phpbu\App\Backup\Target;

/**
 * Abstraction
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.0
 */
interface Decompressable
{
    /**
     * Return extraction command for given filename
     *
     * @param \phpbu\App\Backup\Target $target
     * @return string
     */
    public function decompress(Target $target): string;
}
