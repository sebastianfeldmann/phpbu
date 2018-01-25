<?php
namespace phpbu\App;

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
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Run phpbu
     *
     * @param  \phpbu\App\Configuration $configuration
     * @param  \phpbu\App\Factory
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function run(Configuration $configuration)
    {
        $result = new Result();
        $this->setupLoggers($configuration, $result);

        $backupRunner = new Runner\Backup($this->factory, $result);
        return $backupRunner->run($configuration);
    }

    /**
     * Create and register all configured loggers.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    protected function setupLoggers(Configuration $configuration, Result $result)
    {
        foreach ($configuration->getLoggers() as $log) {
            // this is a already fully setup Listener so just add it
            if ($log instanceof Listener) {
                $logger = $log;
            } else {
                // this is a configuration blueprint for a logger, so create and add it
                /** @var \phpbu\App\Configuration\Logger $log */
                /** @var \phpbu\App\Listener $logger */
                $logger = $this->factory->createLogger($log->type, $log->options);
            }
            $result->addListener($logger);
        }
    }
}
