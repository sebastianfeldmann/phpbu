<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Str;
use RuntimeException;

/**
 * Cleanup backup directory.
 *
 * Removes all files older then a given offset.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Outdated extends Abstraction implements Simulator
{
    /**
     * Original XML value
     *
     * @var string
     */
    protected $offsetRaw;

    /**
     * Offset in seconds
     *
     * @var integer
     */
    protected $offsetSeconds;

    /**
     * Setup the Cleaner
     *
     * @see    \phpbu\App\Backup\Cleanup::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Cleaner\Exception
     */
    public function setup(array $options)
    {
        if (!isset($options['older'])) {
            throw new Exception('option \'older\' is missing');
        }
        try {
            $seconds = Str::toTime($options['older']);
        } catch (RuntimeException $e) {
            throw new Exception($e->getMessage());
        }
        $this->offsetRaw     = $options['older'];
        $this->offsetSeconds = $seconds;
    }

    /**
     * Return list of files to delete
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File\Local[]
     */
    protected function getFilesToDelete(Target $target, Collector $collector)
    {
        $minTime = time() - $this->offsetSeconds;
        $files   = $collector->getBackupFiles();
        $delete  = [];
        $this->result->debug('  found ' . count($files) . ' backup files');

        /** @var \phpbu\App\Backup\File\Local $file */
        foreach ($files as $file) {
            $this->result->debug('  checking ' . $file->getFilename() . ' ' . date('Y.m.d H:i:s', $file->getMTime()));
            // last mod date < min date? delete!
            if ($file->getMTime() < $minTime) {
                $delete[] = $file;
            }
        }

        return $delete;
    }
}
