<?php
namespace phpbu\App\Backup\Cleaner\Stepwise\Keeper;

use phpbu\App\Backup\Cleaner\Stepwise\Keeper;
use phpbu\App\Backup\File;

/**
 * Keep one backup per date group class
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class OnePerGroup implements Keeper
{
    /**
     * Grouping date format f.e. 'Ymd'.
     *
     * @var string
     */
    private $group;

    /**
     * List of groups containing the files.
     *
     * @var \phpbu\App\Backup\File\Local[][]
     */
    private $groups = [];

    /**
     * OnePerGroup constructor.
     *
     * @param string $group
     */
    public function __construct(string $group)
    {
        $this->group = $group;
    }

    /**
     * Decides if given file should be kept.
     *
     * @param  \phpbu\App\Backup\File $file
     * @return bool
     */
    public function keep(File $file) : bool
    {
        $group                  = date($this->group, $file->getMTime());
        $this->groups[$group][] = $file;

        // keep only the first file
        return count($this->groups[$group]) < 2;
    }
}
