<?php
namespace phpbu\App\Configuration\Backup;

use phpbu\App\Exception;

/**
 * Target Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Target
{
    /**
     * Directory.
     *
     * @var string
     */
    public $dirname;

    /**
     * Filename.
     *
     * @var string
     */
    public $filename;

    /**
     * Compression to use.
     *
     * @var string
     */
    public $compression;

    /**
     * Constructor.
     *
     * @param  string $dir
     * @param  string $file
     * @param  string $compression
     * @throws \phpbu\App\Exception
     */
    public function __construct($dir, $file, $compression = null)
    {
        // check dirname and filename
        if ($dir == '' || $file == '') {
            throw new Exception('dirname and filename must be set');
        }
        $this->dirname  = $dir;
        $this->filename = $file;

        if (!empty($compression)) {
            $this->compression = $compression;
        }
    }
}
