<?php
namespace phpbu\App\Event\Backup;

use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration\Backup;
use phpbu\App\Event\Action;

/**
 * Backup event base class.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
abstract class Abstraction extends Action
{
    private $target;
    private $source;

    /**
     * Constructor.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Source $source
     */
    public function __construct(Backup $backup, Target $target, Source $source)
    {
        $this->configuration = $backup;
        $this->target = $target;
        $this->source = $source;
    }

    /**
     * @return \phpbu\App\Backup\Target
     */
    public function getTarget(): Target
    {
        return $this->target;
    }

    /**
     * @return \phpbu\App\Backup\Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }
}
