<?php
namespace phpbu\App;

use phpbu\App\Result;
use phpbu\App\ResultPrinter;
use phpbu\Backup;

/**
 * Runner actually executes all backup jobs.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Runner
{
    /**
     * Application output
     *
     * @var phpbu\App\Listener
     */
    protected $printer;

    /**
     * Application Result
     *
     * @var phpbu\App\Result
     */
    protected $result;

    /**
     * Run all backups configured
     *
     * @param  array $arguments
     * @return phpbu\App\Result
     */
    public function run(array $arguments)
    {
        $this->handleConfiguration($arguments);

        $this->printer = $this->createPrinter($arguments);
        $result        = new Result();
        $result->addListener($this->printer);

        $result->phpbuStart();

        // create backups
        foreach ($arguments['backups'] as $backup) {
            // create target
            $target = new Backup\Target(
                $backup['target']['dirname'],
                $backup['target']['filename']
            );
            // compressor
            if (isset($backup['target']['compress'])) {
                $compressor = Backup\Compressor::create($backup['target']['compress']);
                $target->setCompressor($compressor);
            }
            /*
             *      __               __
             *     / /_  ____ ______/ /____  ______
             *    / __ \/ __ `/ ___/ //_/ / / / __ \
             *   / /_/ / /_/ / /__/ ,< / /_/ / /_/ /
             *  /_.___/\__,_/\___/_/|_|\__,_/ .___/
             *                             /_/
             */
            $result->backupStart($backup);
            $source = Backup\Source\Factory::create($backup['source']['type'], $backup['source']['options']);
            $source->backup($target, $result);
            $result->backupEnd($backup);

            /*
             *          __              __
             *    _____/ /_  ___  _____/ /_______
             *   / ___/ __ \/ _ \/ ___/ //_/ ___/
             *  / /__/ / / /  __/ /__/ ,< (__  )
             *  \___/_/ /_/\___/\___/_/|_/____/
             *
             */
            foreach ($backup['checks'] as $check) {
                $result->checkStart($check);
                // TODO: do check stuff
                $result->checkEnd($check);
            }

            /*
             *     _______  ______  __________
             *    / ___/ / / / __ \/ ___/ ___/
             *   (__  ) /_/ / / / / /__(__  )
             *  /____/\__, /_/ /_/\___/____/
             *       /____/
             */
            foreach ($backup['syncs'] as $sync) {
                $result->syncStart($sync);
                // TODO: do sync stuff
                $result->syncEnd($sync);
            }

            /*
             *          __
             *    _____/ /__  ____ _____  __  ______
             *   / ___/ / _ \/ __ `/ __ \/ / / / __ \
             *  / /__/ /  __/ /_/ / / / / /_/ / /_/ /
             *  \___/_/\___/\__,_/_/ /_/\__,_/ .___/
             *                              /_/
             */
            if (!empty($arguments['cleanup'])) {
                $cleanup = $arguments['cleanup'];
                $result->cleanupStart($cleanup);
                // TODO: do cleanup stuff
                $result->cleanupEnd($cleanup);
            }

        }

        $result->phpbuEnd();

        $this->printer->printResult();

        return $result;
    }

    /**
     * Make sure the config is valid.
     *
     * @param array $arguments
     */
    protected function handleConfiguration(array &$arguments)
    {
        $arguments['colors']  = isset($arguments['colors'])  ? $arguments['colors']  : false;
        $arguments['debug']   = isset($arguments['debug'])   ? $arguments['debug']   : false;
        $arguments['verbose'] = isset($arguments['verbose']) ? $arguments['verbose'] : false;
    }

    /**
     * Creates the output printer.
     *
     * @param  array $arguments
     * @return phpbu\App\ResultPrinter
     */
    protected function createPrinter(array $arguments)
    {
        $printer = new ResultPrinter(
            isset($arguments['stderr']) ? 'php://stderr' : null,
            $arguments['verbose'],
            $arguments['colors'],
            $arguments['debug']
        );

        return $printer;
    }
}
