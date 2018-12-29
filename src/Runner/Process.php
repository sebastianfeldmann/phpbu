<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Result;
use phpbu\App\Factory;

/**
 * Runner base class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 * @internal
 */
abstract class Process
{
    /**
     * @var \phpbu\App\Factory
     */
    protected $factory;

    /**
     * phpbu Result
     *
     * @var \phpbu\App\Result
     */
    protected $result;

    /**
     * phpbu Configuration
     *
     * @var \phpbu\App\Configuration
     */
    protected $configuration;

    /**
     * Backup constructor.
     *
     * @param \phpbu\App\Factory $factory
     * @param \phpbu\App\Result  $result
     */
    public function __construct(Factory $factory, Result $result)
    {
        $this->factory = $factory;
        $this->result  = $result;
    }

    /**
     * Execution blueprint.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     */
    abstract public function run(Configuration $configuration) : Result;
}
