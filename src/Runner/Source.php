<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Source as SourceExe;
use phpbu\App\Backup\Source\Simulator;
use phpbu\App\Backup\Source\Status;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;

/**
 * Backup Runner
 *
 * @package    phpbu
 * @subpackage app
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Source
{
    /**
     * Run a simulation
     *
     * @var bool
     */
    private $isSimulation;

    /**
     * Executes the backup and compression.
     *
     * @param  \phpbu\App\Backup\Source $source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @param  bool                     $simulate
     * @throws \phpbu\App\Exception
     */
    public function run(SourceExe $source, Target $target, Result $result, $simulate)
    {
        $this->isSimulation = $simulate;
        $action             = $simulate ? 'simulate' : 'backup';
        $this->{$action}($source, $target, $result);
    }

    /**
     * Executes the backup and compression.
     *
     * @param  \phpbu\App\Backup\Source $source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    protected function backup(SourceExe $source, Target $target, Result $result)
    {
        $status = $source->backup($target, $result);
        $this->compress($status, $target, $result);
    }

    /**
     * Simulates the backup.
     *
     * @param  \phpbu\App\Backup\Source $source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    protected function simulate(SourceExe $source, Target $target, Result $result)
    {
        if ($source instanceof Simulator) {
            $status = $source->simulate($target, $result);
            $this->compress($status, $target, $result);
        }
    }

    /**
     * Compress the backup if the source did not handle the compression.
     *
     * @param  \phpbu\App\Backup\Source\Status $status
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Result               $result
     * @throws \phpbu\App\Exception
     */
    protected function compress(Status $status, Target $target, Result $result)
    {
        if ($target->shouldBeCompressed() && !$status->handledCompression()) {
            $this->handleCompression($target, $result, $status->getDataPath());
        }
    }

    /**
     * Handle directory compression for sources which can't handle compression by them self.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @param  string                   $dataToCompress
     * @throws \phpbu\App\Exception
     */
    private function handleCompression(Target $target, Result $result, $dataToCompress)
    {
        // is backup data a directory or a file
        if (is_dir($dataToCompress)) {
            $this->compressDirectory($target, $result, $dataToCompress);
        } else {
            $this->compressFile($target, $result, $dataToCompress);
        }
    }

    /**
     * Compresses the target if the target is a directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @param  string                   $dataToCompress
     * @throws \phpbu\App\Exception
     */
    private function compressDirectory(Target $target, Result $result, $dataToCompress)
    {
        // archive data
        $dirCompressor = new Compressor\Directory($dataToCompress);
        $archiveFile   = $this->executeCompressor($dirCompressor, $target, $result);

        // directory is archived but not compressed because tar couldn't handle the compression
        if (!$dirCompressor->canCompress($target)) {
            $this->compressFile($target, $result, $archiveFile);
        }
    }

    /**
     * Compresses the target if the target is a single file.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @param  string                   $dataToCompress
     * @throws \phpbu\App\Exception
     */
    private function compressFile(Target $target, Result $result, $dataToCompress)
    {
        $fileCompressor = new Compressor\File($dataToCompress);
        $this->executeCompressor($fileCompressor, $target, $result);
    }

    /**
     * Execute the compressor.
     * Returns the path to the created archive file.
     *
     * @param  \phpbu\App\Backup\Compressor\Executable $compressor
     * @param  \phpbu\App\Backup\Target                $target
     * @param  \phpbu\App\Result                       $result
     * @return string
     */
    private function executeCompressor(Compressor\Executable $compressor, Target $target, Result $result)
    {
        // if this is a simulation just debug the command that would have been executed
        if ($this->isSimulation) {
            $result->debug($compressor->getExecutable($target)->getCommandLine());
            return $compressor->getArchiveFile($target);
        } else {
            return $compressor->compress($target, $result);
        }
    }
}
