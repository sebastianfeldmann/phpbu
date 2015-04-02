<?php
namespace phpbu\App\Configuration\Loader;

use DOMElement;
use DOMXPath;
use phpbu\App\Configuration;
use phpbu\App\Configuration\Loader;
use phpbu\App\Exception;
use phpbu\App\Util\Cli;
use phpbu\App\Util\String;

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
 *       <check type="sizemin" value="10MB" />
 *
 *       <crypt type="mcrypt">
 *         <option name="algorithm" value="blowfish"/>
 *         <option name="key" value="myKey"/>
 *       </crypt>
 *
 *       <sync type="sftp" skipOnFailure="true">
 *         <option name="host" value="example.com" />
 *         <option name="user" value="user.name" />
 *         <option name="password" value="topsecret" />
 *         <option name="path" value="backup" />
 *       </sync>
 *
 *       <cleanup type="Outdated" skipOnFailure="true">
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
class Xml implements Loader
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
     * Constructor.
     *
     * @param  string $file
     * @throws \phpbu\App\Exception
     */
    public function __construct($file)
    {
        $this->filename = $file;
        $this->document = $this->loadXmlFile($file);
        $this->xpath    = new DOMXPath($this->document);
    }

    /**
     * Returns the phpbu Configuration.
     *
     * @return \phpbu\App\Configuration
     */
    public function getConfiguration()
    {
        $configuration = new Configuration($this->filename);

        $this->setAppSettings($configuration);
        $this->setPhpSettings($configuration);
        $this->setLoggers($configuration);
        $this->setBackups($configuration);

        return $configuration;
    }

    /**
     * Set the phpbu application settings.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    public function setAppSettings(Configuration $configuration)
    {
        $root = $this->document->documentElement;

        if ($root->hasAttribute('bootstrap')) {
            $configuration->setBootstrap($this->toAbsolutePath((string) $root->getAttribute('bootstrap')));
        }
        if ($root->hasAttribute('verbose')) {
            $configuration->setVerbose(String::toBoolean((string) $root->getAttribute('verbose'), false));
        }
        if ($root->hasAttribute('colors')) {
            $configuration->setColors(String::toBoolean((string) $root->getAttribute('colors'), false));
        }
    }

    /**
     * Set the php settings.
     * Checking for include_path and ini settings.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    public function setPhpSettings(Configuration $configuration)
    {
        foreach ($this->xpath->query('php/includePath') as $includePath) {
            $path = (string) $includePath->nodeValue;
            if ($path) {
                $configuration->addIncludePath($this->toAbsolutePath($path));
            }
        }
        foreach ($this->xpath->query('php/ini') as $ini) {
            /** @var DOMElement $ini */
            $name  = (string) $ini->getAttribute('name');
            $value = (string) $ini->getAttribute('value');

            $configuration->addIniSetting($name, $value);
        }
    }

    /**
     * Set the backup configurations.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public function setBackups(Configuration $configuration)
    {
        foreach ($this->xpath->query('backups/backup') as $backupNode) {
            $configuration->addBackup($this->getBackupConfig($backupNode));
        }
    }

    /**
     * Get the config for a single backup node.
     *
     * @param  \DOMElement $backupNode
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Configuration\Backup
     */
    private function getBackupConfig(DOMElement $backupNode)
    {
        $stopOnFailure = String::toBoolean((string) $backupNode->getAttribute('stopOnFailure'), false);
        $backupName    = $backupNode->getAttribute('name');
        $backup        = new Configuration\Backup($backupName, $stopOnFailure);

        $backup->setSource($this->getSource($backupNode));
        $backup->setTarget($this->getTarget($backupNode));

        $this->setChecks($backup, $backupNode);
        $this->setCrypt($backup, $backupNode);
        $this->setSyncs($backup, $backupNode);
        $this->setCleanup($backup, $backupNode);

        return $backup;
    }

    /**
     * Get source configuration.
     *
     * @param  \DOMElement $node
     * @return \phpbu\App\Configuration\Backup\Source
     * @throws \phpbu\App\Exception
     */
    protected function getSource(DOMElement $node)
    {
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

        return new Configuration\Backup\Source($type, $this->getOptions($sourceNode));
    }

    /**
     * Get Target configuration.
     *
     * @param  \DOMElement $node
     * @return \phpbu\App\Configuration\Backup\Target
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

        return new Configuration\Backup\Target($dirname, $filename, $compress);
    }

    /**
     * Set backup checks.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \DOMElement                     $node
     */
    protected function setChecks(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var DOMElement $checkNode */
        foreach ($node->getElementsByTagName('check') as $checkNode) {

            $type  = (string) $checkNode->getAttribute('type');
            $value = (string) $checkNode->getAttribute('value');
            // skip invalid sanity checks
            if (!$type || !$value) {
                continue;
            }
            $backup->addCheck(new Configuration\Backup\Check($type, $value));
        }
    }

    /**
     * Set the crypt configuration.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \DOMElement                     $node
     */
    protected function setCrypt(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var DOMElement $cryptNode */
        foreach ($node->getElementsByTagName('crypt') as $cryptNode) {
            $crypt = new Configuration\Backup\Crypt(
                (string) $cryptNode->getAttribute('type'),
                String::toBoolean((string) $cryptNode->getAttribute('skipOnFailure'), true),
                $this->getOptions($cryptNode)
            );
            $backup->setCrypt($crypt);
        }
    }

    /**
     * Set backup sync configurations.
     *
     * @param Configuration\Backup $backup
     * @param  \DOMElement         $node
     */
    protected function setSyncs(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var DOMElement $syncNode */
        foreach ($node->getElementsByTagName('sync') as $syncNode) {
            $backup->addSync(
                new Configuration\Backup\Sync(
                    (string) $syncNode->getAttribute('type'),
                    String::toBoolean((string) $syncNode->getAttribute('skipOnFailure'), true),
                    $this->getOptions($syncNode)
                )
            );
        }
    }

    /**
     * Set the cleanup configuration.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \DOMElement                     $node
     */
    protected function setCleanup(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var DOMElement $cleanupNode */
        foreach ($node->getElementsByTagName('cleanup') as $cleanupNode) {
            $backup->setCleanup(
                new Configuration\Backup\Cleanup(
                    (string) $cleanupNode->getAttribute('type'),
                    String::toBoolean((string) $cleanupNode->getAttribute('skipOnFailure'), true),
                    $this->getOptions($cleanupNode)
                )
            );
        }
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
     * Set the log configuration.
     *
     * @param \phpbu\App\Configuration $configuration
     */
    public function setLoggers(Configuration $configuration)
    {
        /** @var DOMElement $logNode */
        foreach ($this->xpath->query('logging/log') as $logNode) {
            $options = array();
            $tarAtr  = (string) $logNode->getAttribute('target');
            if (!empty($tarAtr)) {
                $options['target'] = $this->toAbsolutePath($tarAtr);
            }

            /** @var DOMElement $optionNode */
            foreach ($logNode->getElementsByTagName('option') as $optionNode) {
                $name  = (string) $optionNode->getAttribute('name');
                $value = (string) $optionNode->getAttribute('value');
                // check for path option
                if ('target' == $name) {
                    $value = $this->toAbsolutePath($value);
                }
                $options[$name] = $value;
            }
            $configuration->addLogger(
                new Configuration\Logger(
                    (string) $logNode->getAttribute('type'),
                    $options
                )
            );
        }
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
