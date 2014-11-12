<?php
namespace phpbu\App;

use phpbu\App\Listener;

/**
 * Default app output.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class ResultPrinter implements Listener
{
    /**
     * @var boolean
     */
    protected $verbose;

    /**
     * @var boolean
     */
    protected $colors;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param string $out
     * @param string $verbose
     * @param string $colors
     * @param string $debug
     */
    public function __construct($out = null, $verbose = false, $colors = false, $debug = false)
    {
        $this->verbose = $verbose;
        $this->colors  = $colors;
        $this->debug   = $debug;
    }

    /**
     *
     */
    public function phpbuStart()
    {
        if ( $this->verbose ) {
            print 'phpbu starting' . PHP_EOL;
        }
    }

    /**
     *
     */
    public function phpbuEnd()
    {
        if ( $this->verbose ) {
            print 'phpbu done' . PHP_EOL;
        }
    }

    /**
     * @param Backup $backup
     */
    public function backupStart($backup)
    {
        if ( $this->verbose ) {
            print 'starting backup' . PHP_EOL;
        }
    }

    /**
     * @param Backup $backup
     */
    public function backupFailed($backup)
    {
        print 'error performing backup' . PHP_EOL;
    }

    /**
     * @param Backup $backup
     */
    public function backupEnd($backup)
    {
        if ( $this->verbose ) {
            print 'backup done' . PHP_EOL;
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityStart($sanity)
    {
        if ( $this->verbose ) {
            print 'sanity check:';
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityFailed($sanity)
    {
        if ( $this->verbose ) {
            print 'failed' . PHP_EOL;
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityEnd($sanity)
    {
        if ( $this->verbose ) {
            print 'done' . PHP_EOL;
        }
    }

    /**
     * @param Sync $sync
     */
    public function syncStart($sync)
    {
        if ( $this->verbose ) {
            print 'sync start:';
        }
    }

    /**
     * @param Sync $sync
     */
    public function syncFailed($sync)
    {
        if ( $this->verbose ) {
            print 'failed' . PHP_EOL;
        }
    }

    /**
     * @param Sysc $sync
     */
    public function syncEnd($sync)
    {
        if ( $this->verbose ) {
            print 'done' . PHP_EOL;
        }
    }
}