<?php
namespace phpbu\App\Backup;

use phpbu\App\Util\Str;

/**
 * Collector class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
abstract class Collector
{
    /**
     * Backup target
     *
     * @var \phpbu\App\Backup\Target
     */
    protected $target;

    /**
     * Target filename regex
     *
     * @var string
     */
    protected $fileRegex;

    /**
     * Collection cache
     *
     * @var \phpbu\App\Backup\File[]
     */
    protected $files;

    /**
     * Setting up
     *
     * @param \phpbu\App\Backup\Target $target
     */
    public function setUp(Target $target)
    {
        $this->target = $target;
        $this->fileRegex = Str::datePlaceholdersToRegex($target->getFilenameRaw());
        $this->files     = [];
    }

    /**
     * Returns true if filename matches the target regex
     *
     * @param string $filename
     * @return bool
     */
    protected function isFilenameMatch(string $filename): bool
    {
        return preg_match('#'.$this->fileRegex . '#i', $filename);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    abstract public function getBackupFiles() : array;
}
