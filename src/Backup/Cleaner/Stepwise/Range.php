<?php
namespace phpbu\App\Backup\Cleaner\Stepwise;

use phpbu\App\Backup\File;

/**
 * Range with start and end date.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Range
{
    /**
     * Start timestamp.
     *
     * @var int
     */
    private $start;

    /**
     * End timestamp.
     *
     * @var int
     */
    private $end;

    /**
     * Keeper decides if file should be deleted.
     *
     * @var \phpbu\App\Backup\Cleaner\Stepwise\Keeper
     */
    private $keeper;

    /**
     * Range constructor.
     *
     * @param int                                       $start
     * @param int                                       $end
     * @param \phpbu\App\Backup\Cleaner\Stepwise\Keeper $keeper
     */
    public function __construct(int $start, int $end, Keeper $keeper)
    {
        $this->start  = $start;
        $this->end    = $end;
        $this->keeper = $keeper;
    }

    /**
     * Start timestamp getter.
     *
     * @return int
     */
    public function getStart() : int
    {
        return $this->start;
    }

    /**
     * End timestamp getter.
     *
     * @return int
     */
    public function getEnd() : int
    {
        return $this->end;
    }

    /**
     * Should this file be deleted.
     *
     * @param  \phpbu\App\Backup\File $file
     * @return bool
     */
    public function keep(File $file) : bool
    {
        return $this->keeper->keep($file);
    }
}
