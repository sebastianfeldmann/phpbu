<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Source as SourceExe;
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
     * Executes the backup and compression.
     *
     * @param  \phpbu\App\Backup\Source $source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    public function run(SourceExe $source, Target $target, Result $result)
    {
        $status = $source->backup($target, $result);
        if ($target->shouldBeCompressed()) {
            if (is_a($status, '\\phpbu\\App\\Backup\\Source\\Status') && !$status->handledCompression()) {
                $this->handleCompression($target, $result, $status->getDataPath());
            }
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
    protected function handleCompression(Target $target, Result $result, $dataToCompress)
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
    protected function compressDirectory(Target $target, Result $result, $dataToCompress)
    {
        // archive data
        $dirCompressor = new Compressor\Directory($dataToCompress);
        $dirCompressor->compress($target, $result);
        // directory is archived but not compressed because tar couldn't handle the compression
        if (!file_exists($target->getPathname()) && file_exists($target->getPathnamePlain())) {
            // compress the tar with the configured compression
            $this->compressFile($target, $result, $target->getPathnamePlain());
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
    protected function compressFile(Target $target, Result $result, $dataToCompress)
    {
        $fileCompressor = new Compressor\File($dataToCompress);
        $fileCompressor->compress($target, $result);
    }
}
