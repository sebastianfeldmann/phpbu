<?php
namespace phpbu\App\Configuration\Loader;

use DOMDocument;
use DOMElement;
use DOMXPath;
use phpbu\App\Configuration;
use phpbu\App\Configuration\Loader;
use phpbu\App\Exception;
use phpbu\App\Util\Str;

use function defined;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function trim;

/**
 * Loader for a phpbu XML configuration file.
 *
 * Example XML configuration file:
 * <code>
 * <?xml version="1.0" encoding="UTF-8" ?>
 * <phpbu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 *        xsi:noNamespaceSchemaLocation="https://www.phpbu.de/schema/1.1/phpbu.xsd"
 *        bootstrap="backup/bootstrap.php"
 *        verbose="true">
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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Xml extends File implements Loader
{
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
     * Xml constructor.
     *
     * @param  string                                $file
     * @param  \phpbu\App\Configuration\Bootstrapper $bootstrapper
     * @throws \phpbu\App\Exception
     */
    public function __construct(string $file, Configuration\Bootstrapper $bootstrapper)
    {
        parent::__construct($file, $bootstrapper);
        $this->document = $this->loadXmlFile($file);
        $this->xpath    = new DOMXPath($this->document);

        $this->validateConfigurationAgainstSchema();
    }

    /**
     * Return list of adapter configs.
     *
     * @return array
     * @throws \phpbu\App\Exception
     */
    protected function getAdapterConfigs()
    {
        $adapters = [];
        /** @var \DOMElement $adapterNode */
        foreach ($this->xpath->query('adapters/adapter') as $adapterNode) {
            $type    = $adapterNode->getAttribute('type');
            $name    = $adapterNode->getAttribute('name');
            $options = $this->getOptions($adapterNode);
            if (!$type) {
                throw new Exception('invalid adapter configuration: attribute type missing');
            }
            if (!$name) {
                throw new Exception('invalid adapter configuration: attribute name missing');
            }
            $adapters[] = new Configuration\Adapter($type, $name, $options);
        }
        return $adapters;
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
            $configuration->setBootstrap($this->toAbsolutePath($root->getAttribute('bootstrap')));
        }
        if ($root->hasAttribute('verbose')) {
            $configuration->setVerbose(Str::toBoolean($root->getAttribute('verbose'), false));
        }
        if ($root->hasAttribute('colors')) {
            $configuration->setColors(Str::toBoolean($root->getAttribute('colors'), false));
        }
        if ($root->hasAttribute('debug')) {
            $configuration->setDebug(Str::toBoolean($root->getAttribute('debug'), false));
        }
    }

    /**
     * Set the log configuration.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public function setLoggers(Configuration $configuration)
    {
        /** @var \DOMElement $logNode */
        foreach ($this->xpath->query('logging/log') as $logNode) {
            $type = $logNode->getAttribute('type');
            if (!$type) {
                throw new Exception('invalid logger configuration: attribute type missing');
            }
            $options = $this->getOptions($logNode);
            if (isset($options['target'])) {
                $options['target'] = $this->toAbsolutePath($options['target']);
            }
            // search for target attribute to convert to option
            $target = $logNode->getAttribute('target');
            if (!empty($target)) {
                $options['target'] = $this->getAdapterizedValue($this->toAbsolutePath($target));
            }
            $configuration->addLogger(new Configuration\Logger($type, $options));
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
        $stopOnFailure = Str::toBoolean($backupNode->getAttribute('stopOnFailure'), false);
        $backupName    = $this->getAdapterizedValue($backupNode->getAttribute('name'));
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
        $type       = $sourceNode->getAttribute('type');
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
        $compress   = $targetNode->getAttribute('compress');
        $filename   = $this->getAdapterizedValue($targetNode->getAttribute('filename'));
        $dirname    = $this->getAdapterizedValue($targetNode->getAttribute('dirname'));

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
            $type  = $checkNode->getAttribute('type');
            $value = $checkNode->getAttribute('value');
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
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \DOMElement                     $node
     * @throws \phpbu\App\Exception
     */
    protected function setCrypt(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var \DOMNodeList $cryptNodes */
        $cryptNodes = $node->getElementsByTagName('crypt');
        if ($cryptNodes->length > 0) {
            /** @var \DOMElement $cryptNode */
            $cryptNode = $cryptNodes->item(0);
            $type = $cryptNode->getAttribute('type');
            if (!$type) {
                throw new Exception('invalid crypt configuration: attribute type missing');
            }
            $skip    = Str::toBoolean($cryptNode->getAttribute('skipOnFailure'), true);
            $options = $this->getOptions($cryptNode);
            $backup->setCrypt(new Configuration\Backup\Crypt($type, $skip, $options));
        }
    }

    /**
     * Set backup sync configurations.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \DOMElement                     $node
     * @throws \phpbu\App\Exception
     */
    protected function setSyncs(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var DOMElement $syncNode */
        foreach ($node->getElementsByTagName('sync') as $syncNode) {
            $type = $syncNode->getAttribute('type');
            if (!$type) {
                throw new Exception('invalid sync configuration: attribute type missing');
            }
            $skip    = Str::toBoolean($syncNode->getAttribute('skipOnFailure'), true);
            $options = $this->getOptions($syncNode);
            $backup->addSync(new Configuration\Backup\Sync($type, $skip, $options));
        }
    }

    /**
     * Set the cleanup configuration.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \DOMElement                     $node
     * @throws \phpbu\App\Exception
     */
    protected function setCleanup(Configuration\Backup $backup, DOMElement $node)
    {
        /** @var \DOMNodeList $cleanupNodes */
        $cleanupNodes = $node->getElementsByTagName('cleanup');
        if ($cleanupNodes->length > 0) {
            /** @var \DOMElement $cleanupNode */
            $cleanupNode = $cleanupNodes->item(0);
            $type        = $cleanupNode->getAttribute('type');
            if (!$type) {
                throw new Exception('invalid cleanup configuration: attribute type missing');
            }
            $skip    = Str::toBoolean($cleanupNode->getAttribute('skipOnFailure'), true);
            $options = $this->getOptions($cleanupNode);
            $backup->setCleanup(new Configuration\Backup\Cleanup($type, $skip, $options));
        }
    }

    /**
     * Extracts all option tags.
     *
     * @param  \DOMElement $node
     * @return array
     * @throws \phpbu\App\Exception
     */
    protected function getOptions(DOMElement $node)
    {
        $options = [];
        /** @var \DOMElement $optionNode */
        foreach ($node->getElementsByTagName('option') as $optionNode) {
            $name           = $optionNode->getAttribute('name');
            $value          = $this->getAdapterizedValue($optionNode->getAttribute('value'));
            $options[$name] = $value;
        }
        return $options;
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
        $contents  = $this->loadFile($filename);
        $document  = new DOMDocument;
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

    /**
     * Validate xml configuration against phpbu.xsd schema
     *
     * @return void
     */
    private function validateConfigurationAgainstSchema()
    {
        $original    = libxml_use_internal_errors(true);
        $xsdFilename = __DIR__ . '/../../../phpbu.xsd';
        if (defined('__PHPBU_PHAR_ROOT__')) {
            $xsdFilename = __PHPBU_PHAR_ROOT__ . '/phpbu.xsd';
        }
        $this->document->schemaValidate($xsdFilename);
        foreach (libxml_get_errors() as $error) {
            if (!isset($this->errors[$error->line])) {
                $this->errors[$error->line] = [];
            }
            $this->errors[$error->line][] = trim($error->message);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($original);
    }
}
