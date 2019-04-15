<?php
namespace phpbu\App;

use phpbu\App\Runner\Process;

/**
 * Runner actually executes all backup jobs.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Runner
{
    /**
     * phpbu Factory
     *
     * @var \phpbu\App\Factory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param \phpbu\App\Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Factory getter
     *
     * @return \phpbu\App\Factory
     */
    public function getFactory() : Factory
    {
        return $this->factory;
    }

    /**
     * Run phpbu
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function run(Configuration $configuration) : Result
    {
        $result = new Result();
        $this->setupLoggers($configuration, $result);

        $runner = $this->createRunner($configuration, $result);
        return $runner->run($configuration);
    }

    /**
     * Create and register all configured loggers.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    private function setupLoggers(Configuration $configuration, Result $result) : void
    {
        foreach ($configuration->getLoggers() as $log) {
            // log is a already fully setup Listener so just use it
            if ($log instanceof Listener) {
                $logger = $log;
            } else {
                // put some system configuration values into the logger configuration
                $system  = ['__simulate__' => $configuration->isSimulation()];
                $options = array_merge($log->options, $system);
                // log is a configuration blueprint for a logger, so create it
                /** @var \phpbu\App\Listener $logger */
                $logger = $this->factory->createLogger($log->type, $options);
            }
            $result->addListener($logger);
        }
    }

    /**
     * Create a runner executing the requested process.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Runner\Process
     */
    private function createRunner(Configuration $configuration, Result $result) : Process
    {
        if ($configuration->isSimulation()) {
            return new Runner\Simulate($this->factory, $result);
        }

        if ($configuration->isRestore()) {
            return new Runner\Restore($this->factory, $result);
        }

        return new Runner\Backup($this->factory, $result);
    }
}
