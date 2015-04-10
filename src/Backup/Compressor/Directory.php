<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Source\Tar;
use phpbu\App\Backup\Target;
use phpbu\App\Exception;
use phpbu\App\Result;

/**
 * Directory
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.1
 */
class Directory
{
    /**
     * Path to dir to compress.
     *
     * @var string
     */
    private $path;

    /**
     * Constructor.
     *
     * @param  string $path
     * @throws \phpbu\App\Exception
     */
    public function __construct($path)
    {
        if (empty($path)) {
            throw new Exception('no path to compress set');
        }
        if (!is_dir($path)) {
            throw new Exception('path to compress should be a directory');
        }
        $this->path = $path;
    }

    /**
     * Compress the configured directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    public function compress(Target $target, Result $result)
    {
        try {
            $tar = new Tar();
            $tar->setup(
                array(
                    'path'      => $this->path,
                    'removeDir' => 'true',
                )
            );
            $tar->backup($target, $result);
        } catch (\Exception $e) {
            throw new Exception('Failed to \'tar\' directory: ' . $this->path . PHP_EOL . $e->getMessage(), 1, $e);
        }
    }
}
