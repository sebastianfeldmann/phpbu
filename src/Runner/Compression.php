<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Source\Status;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Compression Runner
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 * @internal
 */
abstract class Compression extends Process
{
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
        // if the target is not compressed yet
        // and should be compressed or at least archived (tar)
        if (!$status->handledCompression() && ($target->shouldBeCompressed() || $status->isDirectory())) {
            $this->handleCompression($target, $result, $status);
        }
    }

    /**
     * Handle directory compression for sources which can't handle compression by them self.
     *
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Result               $result
     * @param  \phpbu\App\Backup\Source\Status $status
     * @throws \phpbu\App\Exception
     */
    protected function handleCompression(Target $target, Result $result, Status $status)
    {
        // is backup data a directory or a file
        if ($status->isDirectory()) {
            $this->compressDirectory($target, $result, $status->getDataPath());
        } else {
            $this->compressFile($target, $result, $status->getDataPath());
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
        $archiveFile   = $this->executeCompressor($dirCompressor, $target, $result);

        // if target should be compressed but tar couldn't handle the compression
        // run extra file compression to compress the tar file
        if ($target->shouldBeCompressed() && !$dirCompressor->canCompress($target)) {
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
    protected function compressFile(Target $target, Result $result, $dataToCompress)
    {
        $fileCompressor = new Compressor\File($dataToCompress);
        $this->executeCompressor($fileCompressor, $target, $result);
    }

    /**
     * Execute the compressor.
     * Returns the path to the created archive file.
     *
     * @param  \phpbu\App\Backup\Compressor\Compressible $compressor
     * @param  \phpbu\App\Backup\Target                  $target
     * @param  \phpbu\App\Result                         $result
     * @return string
     */
    abstract protected function executeCompressor(
        Compressor\Compressible $compressor,
        Target $target,
        Result $result
    ) : string;
}
