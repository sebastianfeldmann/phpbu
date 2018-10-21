<?php
namespace phpbu\App\Backup;

use phpbu\App\Result;

/**
 * Crypter
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.3.0
 */
interface Crypter
{
    /**
     * Setup the Crypter.
     *
     * @param array $options
     */
    public function setup(array $options = []);

    /**
     * Checks the created backup.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result $result
     * @throws \phpbu\App\Exception
     */
    public function crypt(Target $target, Result $result);

    /**
     * Return the encrypted file suffix.
     *
     * @return string
     */
    public function getSuffix();
}
