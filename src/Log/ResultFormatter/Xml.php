<?php
namespace phpbu\App\Log\ResultFormatter;

use phpbu\App\Log\ResultFormatter;
use phpbu\App\Result;

/**
 * Xml ResultFormatter
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Xml extends Abstraction implements ResultFormatter
{
    /**
     * Create request body from phpbu result data.
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    public function format(Result $result): string
    {
        $data = $this->getSummaryData($result);
        $xml  = new \SimpleXMLElement('<phpbu></phpbu>');

        foreach ($data as $name => $value) {
            $xml->addChild($name, $value);
        }

        $errors = $xml->addChild('errors');
        $this->appendErrors($errors, $result->getErrors());

        $backups = $xml->addChild('backups');
        $this->appendBackups($backups, $result->getBackups());

        return $xml->asXML();
    }

    /**
     * Append <error> xml elements to the <errors> tag.
     *
     *   <error>
     *     <class>\Exception</class>
     *     <message>Foo Bar Baz</message>
     *     <file>foo.php</file>
     *     <line>42</line>
     *   </error>
     *
     * @param \SimpleXMLElement $xml
     * @param array             $errors
     */
    private function appendErrors(\SimpleXMLElement $xml, array $errors)
    {
        /* @var $e \Exception */
        foreach ($errors as $e) {
            $error = $xml->addChild('error');
            $error->addChild('class', get_class($e));
            $error->addChild('message', $e->getMessage());
            $error->addChild('file', $e->getFile());
            $error->addChild('line', $e->getLine());
        }
    }

    /**
     * Append <backup> xml elements to the <backups> tag.
     *
     *   <backup>
     *     <name>foo</name>
     *     <status>0</status>
     *     <checkCount>1</checkCount>
     *     <checkFailed>0</checkFailed>
     *     ...
     *   </backup>
     *
     * @param \SimpleXMLElement $xml
     * @param array             $backups
     */
    private function appendBackups(\SimpleXMLElement $xml, array $backups)
    {
        /* @var $b \phpbu\App\Result\Backup */
        foreach ($backups as $b) {
            $backup = $xml->addChild('backup');
            $backup->addChild('name', $b->getName());
            $backup->addChild('status', $b->allOk() ? 0 : 1);

            $checks = $backup->addChild('checks');
            $checks->addChild('executed', $b->checkCount());
            $checks->addChild('failed', $b->checkCountFailed());

            $crypts = $backup->addChild('crypt');
            $crypts->addChild('executed', $b->cryptCount());
            $crypts->addChild('failed', $b->cryptCountFailed());
            $crypts->addChild('skipped', $b->cryptCountSkipped());

            $syncs = $backup->addChild('syncs');
            $syncs->addChild('executed', $b->syncCount());
            $syncs->addChild('failed', $b->syncCountFailed());
            $syncs->addChild('skipped', $b->syncCountSkipped());

            $cleanup = $backup->addChild('cleanup');
            $cleanup->addChild('executed', $b->cleanupCount());
            $cleanup->addChild('failed', $b->cleanupCountFailed());
            $cleanup->addChild('skipped', $b->cleanupCountSkipped());
        }
    }
}
