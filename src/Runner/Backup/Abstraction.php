<?php
namespace phpbu\App\Runner\Backup;

/**
 * Task Runner Interface
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
abstract class Abstraction implements Task
{
    /**
     * Is simulation run.
     *
     * @var bool
     */
    protected $isSimulation;

    /**
     * Set is simulation
     *
     * @param $bool
     */
    public function setSimulation(bool $bool)
    {
        $this->isSimulation = $bool;
    }

    /**
     * Is simulation getter.
     *
     * @return bool
     */
    public function isSimulation() : bool
    {
        return $this->isSimulation;
    }
}
