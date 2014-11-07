<?php
namespace phpbu\Backup;

use phpbu\Backup\Target;

/**
 * Source interface
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Source
{
    /**
     * Constructor
     *
     * @param  Target $target
     * @param  array  $conf
     */
    public function __construct(Target $target, array $conf = array());

    /**
     * Runner getter
     *
     * @return Runner
     */
    public function getRunner();
}
