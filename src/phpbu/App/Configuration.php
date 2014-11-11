<?php
namespace phpbu\App;

use DOMElement;
use DOMXPath;
use phpbu\App\Exception;
use phpbu\Util\String;

/**
 *
 * Wrapper for the phpbu XML configuration file.
 *
 * Example XML configuration file:
 * <code>
 * <?xml version="1.0" encoding="UTF-8" ?>
 * <phpbu bootstrap="backup/bootstrap.php"
 *        verbose="true">
 *
 *   <php>
 *     <includePath>.</includePath>
 *     <ini name="max_execution_time" value="0" />
 *   </php>
 *
 *   <logging>
 *     <log type="json" target="/tmp/logfile.json" />
 *     <log type="plain" target="/tmp/logfile.txt" />
 *   </logging>
 *
 *   <backups>
 *     <backup>
 *       <source type="mysql">
 *         <option name="databases" value="dbname" />
 *         <option name="tables" value="" />
 *         <option name="ignoreTables" value="" />
 *         <option name="structureOnly" value="dbname.table1,dbname.table2" />
 *       </source>
 *
 *       <target dirname="/tmp/backup" filename="mysqldump-%Y%m%d-%H%i.sql" compress="bzip2" />
 *
 *       <sanitycheck type="SomeName" value="10MB" />
 *       <sanitycheck type="SomeName" value="20MB" />
 *
 *       <sync type="rsync" skipOnSanityFail="true">
 *         <option name="user" value="user.name" />
 *         <option name="password" value="topsecret" />
 *       </sync>
 *
 *       <cleanup skipOnSanityFail="true">
 *         <option name="amount" value="50" />
 *         <option name="outdated" value="2W" />
 *       </cleanup>
 *     </backup>
 *   </backups>
 * </phpbu>
 * </code>
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Configuration
{
    /**
     * Path to configfile.
     *
     * @var string
     */
    private $filename;

    /**
     * Configfile DOMDocument
     *
     * @var DOMDocument
     */
    private $document;

    /**
     * Xpath to navigate the config DOM.
     *
     * @var DOMXPath
     */
    private $xpath;

    /**
     * Constructor
     *
     * @param  string $filename
     * @return Configuration
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->document = $this->loadXmlFile($filename);
        $this->xpath    = new DOMXPath($this->document);
    }

    /**
     * Get the phpbu application settings.
     *
     * @return array
     */
    public function getAppSettings()
    {
        $settings = array();
        $root     = $this->document->documentElement;

        if ($root->hasAttribute('bootstrap')) {
            $settings['bootstrap'] = $this->toAbsolutePath((string) $root->getAttribute('bootstrap'));
        }
        if ($root->hasAttribute('verbose')) {
            $settings['verbose'] = String::toBoolean((string) $root->getAttribute('verbose'), false);
        }
        return $settings;
    }

    /**
     * Get the php settings.
     * Checking for include_path and ini settings.
     *
     * @return array
     */
    public function getPhpSettings()
    {
        $settings = array(
            'include_path' => array(),
            'ini'          => array(),
        );
        foreach ($this->xpath->query('php/includePath') as $includePath) {
            $path = (string) $includePath->nodeValue;
            if ($path) {
                $settings['include_path'][] = $this->toAbsolutePath($path);
            }
        }
        foreach ($this->xpath->query('php/ini') as $ini) {
            $name  = (string) $ini->getAttribute('name');
            $value = (string) $ini->getAttribute('value');

            $settings['ini'][$name] = $value;
        }
        return $settings;
    }

    /**
     * Get the backup configurations.
     *
     * @return array
     */
    public function getBackupSettings()
    {
        $settings = array();
        foreach ($this->xpath->query('backups/backup') as $backupNode) {
            $settings[] = $this->getBackupConfig($backupNode);
        }
        return $settings;
    }

    /**
     * Get the config for a single backup node.
     *
     * @param  DOMElement $backupNode
     * @throws phpbu\App\Exception
     * @return array
     */
    private function getBackupConfig(DOMElement $backupNode)
    {
        // get source configuration
        $source  = array();
        $sources = $backupNode->getElementsByTagName('source');
        if ($sources->length !== 1) {
            throw new Exception('backup requires exactly one source config');
        }
        $sourceNode = $sources->item(0);
        $type       = (string) $sourceNode->getAttribute('type');
        if (!$type) {
            throw new Exception('source requires type attribute');
        }
        $source['type'] = $type;
        foreach ($sourceNode->getElementsByTagName('option') as $optionNode) {
            $name                     = (string) $optionNode->getAttribute('name');
            $value                    = (string) $optionNode->getAttribute('value');
            $source['options'][$name] = $value;
        }

        // get target configuration
        $targets = $backupNode->getElementsByTagName('target');
        if ($targets->length !== 1) {
            throw new Exception('backup requires exactly one target config');
        }
        $targetNode = $targets->item(0);
        $compress   = (string) $targetNode->getAttribute('compress');
        $filename   = (string) $targetNode->getAttribute('filename');
        $dirname    = (string) $targetNode->getAttribute('dirname');
        if ($dirname) {
            $dirname = $this->toAbsolutePath($dirname);
        }

        $target = array(
            'dirname'  => $dirname,
            'filename' => $filename,
            'compress' => $compress,
        );

        // get sanity information
        $sanity = array();
        foreach ($backupNode->getElementsByTagName('sanitycheck') as $sanityNode) {
            $type  = (string) $sanityNode->getAttribute('type');
            $value = (string) $sanityNode->getAttribute('value');
            // skip invalid sanity checks
            if (!$type || !$value) {
                continue;
            }
            $sanity[] = array('type' => $type, 'value' => $value);
        }

        // get sync configuration
        $sync = array();
        foreach ($backupNode->getElementsByTagName('sync') as $syncNode) {
            $type    = (string) $syncNode->getAttribute('type');
            $skip    = String::toBoolean((string) $syncNode->getAttribute('skipOnSanityFail'), true);
            $options = array();
            foreach ($syncNode->getElementsByTagName('option') as $optionNode) {
                $name                   = (string) $optionNode->getAttribute('name');
                $value                  = (string) $optionNode->getAttribute('value');
                $sync['options'][$name] = $value;
            }
            $sync[] = array('type' => $type, 'skipOnSanityFail' => $skip, 'options' => $options);
        }

        return array(
            'source' => $source,
            'target' => $target,
            'sanity' => $sanity,
            'sync'   => $sync,
        );
    }

    /**
     * Get the log configuration.
     *
     * @return array
     */
    public function getLoggingSettings()
    {
        $settings = array();
        foreach ($this->xpath->query('logging/log') as $log) {
            $type   = (string) $log->getAttribute('type');
            $target = (string) $log->getAttribute('target');
            $level  = (string) $log->getAttribute('level');

            if (!$target) {
                continue;
            }

            $target = $this->toAbsolutePath($target);
            $conf   = array(
                'type'   => $type,
                'target' => $target,
                'level'  => $level,
            );

            // cronfire logging
            if ($type == 'cronfire') {
                if ($log->hasAttribute('host')) {
                    $conf['host'] = (string) $log->getAttribute('host');
                }
            }
            $settings[] = $conf;
        }
        return $settings;
    }

    /**
     * Converts a path to an absolute one if necessary.
     *
     * @author Sebastian Bergmann <sebastian@phpunit.de>
     * @param  string  $path
     * @param  boolean $useIncludePath
     * @return string
     */
    protected function toAbsolutePath($path, $useIncludePath = false)
    {
        // path already absolute?
        if ($path[0] === '/') {
            return $path;
        }

        // Matches the following on Windows:
        //  - \\NetworkComputer\Path
        //  - \\.\D:
        //  - \\.\c:
        //  - C:\Windows
        //  - C:\windows
        //  - C:/windows
        //  - c:/windows
        if (defined('PHP_WINDOWS_VERSION_BUILD')
         && ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]\:[/\\\]#i', substr($path, 0, 3))))
        ) {
            return $path;
        }

        // Stream
        if (strpos($path, '://') !== false) {
            return $path;
        }

        $file = dirname($this->filename) . DIRECTORY_SEPARATOR . $path;

        if ($useIncludePath && !file_exists($file)) {
            $includePathFile = stream_resolve_include_path($path);
            if ($includePathFile) {
                $file = $includePathFile;
            }
        }
        return $file;
    }

    /**
     * Load the XML-File.
     *
     * @param  string $filename
     * @throws phpbu\App\Exception
     * @return DOMDocument
     */
    private function loadXmlFile($filename)
    {
        $reporting = error_reporting(0);
        $contents  = file_get_contents($filename);
        error_reporting($reporting);

        if ($contents === false) {
            throw new Exception(sprintf('Could not read "%s".', $filename));
        }

        $document  = new \DOMDocument;
        $message   = '';
        $internal  = libxml_use_internal_errors(true);
        $reporting = error_reporting(0);

        $document->documentURI = $filename;
        $loaded                = $document->loadXML($contents);

        foreach (libxml_get_errors() as $error) {
            $message .= "\n" . $error->message;
        }

        libxml_use_internal_errors($internal);
        error_reporting($reporting);

        if ($loaded === false || $message !== '') {
            throw new Exception(
                sprintf(
                    'Error loading file "%s".%s',
                    $filename,
                    $message != '' ? "\n" . $message : ''
                )
            );
        }
        return $document;
    }
}
