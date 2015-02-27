<?php
namespace phpbu\App;

use DOMElement;
use DOMXPath;
use phpbu\Util\Cli;
use phpbu\Util\String;

/**
 *
 * Wrapper for the phpbu XML configuration file.
 *
 * Example XML configuration file:
 * <code>
 * <?xml version="1.0" encoding="UTF-8" ?>
 * <phpbu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 *        xsi:noNamespaceSchemaLocation="http://schema.phpbu.de/1.1/phpbu.xsd"
 *        bootstrap="backup/bootstrap.php"
 *        verbose="true">
 *
 *   <php>
 *     <includePath>.</includePath>
 *     <ini name="max_execution_time" value="0" />
 *   </php>
 *
 *   <logging>
 *     <log type="json" target="/tmp/logfile.json" />
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
 *       <check type="MinSize" value="10MB" />
 *
 *       <sync type="sftp" skipOnSanityFail="true">
 *         <option name="host" value="example.com" />
 *         <option name="user" value="user.name" />
 *         <option name="password" value="topsecret" />
 *         <option name="path" value="backup" />
 *       </sync>
 *
 *       <cleanup type="Outdated" skipOnSanityFail="true">
 *         <option name="older" value="2W" />
 *       </cleanup>
 *     </backup>
 *   </backups>
 * </phpbu>
 * </code>
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Configuration
{
    /**
     * Path to config file.
     *
     * @var string
     */
    private $filename;

    /**
     * Config file DOMDocument
     *
     * @var \DOMDocument
     */
    private $document;

    /**
     * Xpath to navigate the config DOM.
     *
     * @var \DOMXPath
     */
    private $xpath;

    /**
     * Constructor
     *
     * @param  string $filename
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
        if ($root->hasAttribute('colors')) {
            $settings['colors'] = String::toBoolean((string) $root->getAttribute('colors'), false);
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
            /** @var DOMElement $ini */
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
     * @param  \DOMElement $backupNode
     * @throws \phpbu\App\Exception
     * @return array
     */
    private function getBackupConfig(DOMElement $backupNode)
    {
        $stopOnError = String::toBoolean((string) $backupNode->getAttribute('stopOnError'), false);
        $backupName  = $backupNode->getAttribute('name');

        $source  = $this->getSource($backupNode);        
        $target  = $this->getTarget($backupNode);
        $checks  = $this->getChecks($backupNode);
        $syncs   = $this->getSyncs($backupNode);
        $cleanup = $this->getCleanup($backupNode);

        return array(
            'name'        => $backupName,
            'stopOnError' => $stopOnError,
            'source'      => $source,
            'target'      => $target,
            'checks'      => $checks,
            'syncs'       => $syncs,
            'cleanup'     => $cleanup,
        );
    }

    /**
     * Get source configuration.
     * 
     * @param  \DOMElement $node
     * @return array
     * @throws \phpbu\App\Exception
     */
    protected function getSource(DOMElement $node)
    {
        $source  = array();
        $sources = $node->getElementsByTagName('source');
        if ($sources->length !== 1) {
            throw new Exception('backup requires exactly one source config');
        }
        /** @var DOMElement $sourceNode */
        $sourceNode = $sources->item(0);
        $type       = (string) $sourceNode->getAttribute('type');
        if (!$type) {
            throw new Exception('source requires type attribute');
        }
        $source['type']    = $type;
        $source['options'] = $this->getOptions($sourceNode);
        
        return $source;
    }

    /**
     * Get Target configuration.
     * 
     * @param  \DOMElement $node
     * @return array
     * @throws \phpbu\App\Exception
     */
    protected function getTarget(DOMElement $node)
    {
        $targets = $node->getElementsByTagName('target');
        if ($targets->length !== 1) {
            throw new Exception('backup requires exactly one target config');
        }
        /** @var DOMElement $targetNode */
        $targetNode = $targets->item(0);
        $compress   = (string) $targetNode->getAttribute('compress');
        $filename   = (string) $targetNode->getAttribute('filename');
        $dirname    = (string) $targetNode->getAttribute('dirname');
        
        if ($dirname) {
            $dirname = $this->toAbsolutePath($dirname);
        }

        return array(
            'dirname'  => $dirname,
            'filename' => $filename,
            'compress' => $compress,
        );
    }

    /**
     * Get backup checks.
     * 
     * @param  \DOMElement $node
     * @return array
     */
    protected function getChecks(DOMElement $node)
    {
        $checks = array();
        /** @var DOMElement $checkNode */
        foreach ($node->getElementsByTagName('check') as $checkNode) {
            $type  = (string) $checkNode->getAttribute('type');
            $value = (string) $checkNode->getAttribute('value');
            // skip invalid sanity checks
            if (!$type || !$value) {
                continue;
            }
            $checks[] = array('type' => $type, 'value' => $value);
        }
        return $checks;
    }

    /**
     * Get backup sync configurations.
     * 
     * @param  \DOMElement $node
     * @return array
     */
    protected function getSyncs(DOMElement $node)
    {
        $syncs = array();
        /** @var DOMElement $syncNode */
        foreach ($node->getElementsByTagName('sync') as $syncNode) {
            $sync = array(
                'type'            => (string) $syncNode->getAttribute('type'),
                'skipOnCheckFail' => String::toBoolean((string) $syncNode->getAttribute('skipOnCheckFail'), true),
                'options'         => array()
            );

            $sync['options'] = $this->getOptions($syncNode);
            $syncs[]         = $sync;
        }
        return $syncs;
    }

    /**
     * Get the cleanup configuration.
     * 
     * @param  \DOMElement $node
     * @return array
     */
    protected function getCleanup(DOMElement $node)
    {
        $cleanup = array();
        /** @var DOMElement $cleanupNode */
        foreach ($node->getElementsByTagName('cleanup') as $cleanupNode) {
            $cleanup = array(
                'type'            => (string) $cleanupNode->getAttribute('type'),
                'skipOnCheckFail' => String::toBoolean((string) $cleanupNode->getAttribute('skipOnCheckFail'), true),
                'skipOnSyncFail'  => String::toBoolean((string) $cleanupNode->getAttribute('skipOnSyncFail'), true),
                'options'         => array()
            );
            $cleanup['options'] = $this->getOptions($cleanupNode);
        }
        return $cleanup;
    }

    /**
     * Extracts all option tags.
     *
     * @param  DOMElement $node
     * @return array
     */
    protected function getOptions(DOMElement $node)
    {
        $options = array();
        /** @var DOMElement $optionNode */
        foreach ($node->getElementsByTagName('option') as $optionNode) {
            $name           = (string) $optionNode->getAttribute('name');
            $value          = (string) $optionNode->getAttribute('value');
            $options[$name] = $value;
        }
        return $options;
    }

    /**
     * Get the log configuration.
     *
     * @return array
     */
    public function getLoggingSettings()
    {
        $loggers = array();
        /** @var DOMElement $logNode */
        foreach ($this->xpath->query('logging/log') as $logNode) {
            $log = array(
                'type'    => (string) $logNode->getAttribute('type'),
                'options' => array(),
            );
            $tarAtr = (string) $logNode->getAttribute('target');
            if (!empty($tarAtr)) {
                $log['options']['target'] = $this->toAbsolutePath($tarAtr);
            }

            /** @var DOMElement $optionNode */
            foreach ($logNode->getElementsByTagName('option') as $optionNode) {
                $name  = (string) $optionNode->getAttribute('name');
                $value = (string) $optionNode->getAttribute('value');
                // check for path option
                if ('target' == $name) {
                    $value = $this->toAbsolutePath($value);
                }
                $log['options'][$name] = $value;
            }

            $loggers[] = $log;
        }
        return $loggers;
    }

    /**
     * Converts a path to an absolute one if necessary.
     *
     * @param  string  $path
     * @param  boolean $useIncludePath
     * @return string
     */
    protected function toAbsolutePath($path, $useIncludePath = false)
    {
        return Cli::toAbsolutePath($path, dirname($this->filename), $useIncludePath);
    }

    /**
     * Load the XML-File.
     *
     * @param  string $filename
     * @throws \phpbu\App\Exception
     * @return \DOMDocument
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
