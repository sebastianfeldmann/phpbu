<?php
namespace phpbu\App\Log\ResultFormatter;

use phpbu\App\Exception;
use phpbu\App\Log\ResultFormatter;
use phpbu\App\Result;

/**
 * Template ResultFormatter
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Template extends Abstraction implements ResultFormatter
{
    /**
     * Template markup code
     *
     * @var string
     */
    private $template;

    /**
     * Template constructor.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->template = $this->loadBodyFromTemplate($file);
    }

    /**
     * Loads the body template if it exists.
     *
     * @param  string $template
     * @return string
     * @throws \phpbu\App\Exception
     */
    private function loadBodyFromTemplate(string $template) : string
    {
        if (!file_exists($template)) {
            throw new Exception('template not found: ' . $template);
        }
        return file_get_contents($template);
    }

    /**
     * Create request body from phpbu result data.
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    public function format(Result $result): string
    {
        $data           = $this->getSummaryData($result);
        $this->template = $this->renderTemplate($this->template, $data);

        // replace error loop
        // manipulates $this->template
        $this->handleErrors($result);

        // replace backup loop
        // manipulates $this->template
        $this->handleBackups($result);

        return $this->template;
    }

    /**
     * Handles error sub template rendering if necessary.
     *   - Manipulates $this->template
     *
     * @param \phpbu\App\Result $result
     */
    private function handleErrors(Result $result)
    {
        $errorTpl = $this->extractSubTemplate($this->template, 'error');

        if (!empty($errorTpl)) {
            $errorLoopMarkup = $this->renderErrors($errorTpl, $result->getErrors());
            $this->renderLoop('error', $errorLoopMarkup);
        }
    }

    /**
     * Handles backup sub template rendreing if necessary.
     *   - Manipulates $this->template
     *
     * @param \phpbu\App\Result $result
     */
    private function handleBackups(Result $result)
    {
        $backupTpl = $this->extractSubTemplate($this->template, 'backup');

        if (!empty($backupTpl)) {
            $backupLoopMarkup = $this->renderBackups($backupTpl, $result->getBackups());
            $this->renderLoop('backup', $backupLoopMarkup);
        }
    }

    /**
     * Extract loop template.
     *
     * @param  string $template
     * @param  string $loop
     * @return string
     */
    private function extractSubTemplate(string $template, string $loop) : string
    {
        $subTemplate = '';
        $match       = [];
        if (preg_match('#%%' . $loop . '%%([\w\W\s]*)%%' . $loop . '%%#im', $template, $match)) {
            $subTemplate = $match[1];
        }
        return $subTemplate;
    }

    /**
     * Renders errors with extracted error sub template.
     *
     * @param  string $errorTpl
     * @param  array  $errors
     * @return string
     */
    private function renderErrors(string $errorTpl, array $errors) : string
    {
        $markup = '';
        /* @var $e \Exception */
        foreach ($errors as $e) {
            $data   = [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ];
            $markup = $this->renderTemplate($errorTpl, $data);
        }
        return $markup;
    }

    /**
     * Renders backups with the extracted sub template.
     *
     * @param  string $backupTpl
     * @param  array  $backups
     * @return string
     */
    private function renderBackups(string $backupTpl, array $backups) : string
    {
        $markup = '';
        /* @var $b \phpbu\App\Result\Backup */
        foreach ($backups as $b) {
            $data   = [
                'name'           => $b->getName(),
                'status'         => $b->allOk() ? 0 : 1,
                'checkCount'     => $b->checkCount(),
                'checkFailed'    => $b->checkCountFailed(),
                'cryptCount'     => $b->cryptCount(),
                'cryptFailed'    => $b->cryptCountFailed(),
                'cryptSkipped'   => $b->cryptCountSkipped(),
                'syncCount'      => $b->syncCount(),
                'syncFailed'     => $b->syncCountFailed(),
                'syncSkipped'    => $b->syncCountSkipped(),
                'cleanupCount'   => $b->cleanupCount(),
                'cleanupFailed'  => $b->cleanupCountFailed(),
                'cleanupSkipped' => $b->cleanupCountSkipped(),
            ];
            $markup = $this->renderTemplate($backupTpl, $data);
        }
        return $markup;
    }

    /**
     * Replace %name% placeholders in a string with [name => value].
     *
     * @param  string $template
     * @param  array  $data
     * @return string
     */
    private function renderTemplate(string $template, array $data) : string
    {
        foreach ($data as $name => $value) {
            $template = str_replace('%' . $name . '%', $value, $template);
        }
        return $template;
    }

    /**
     * Replace a loop placeholder %%loopname%% with some pre rendered markup.
     *
     * @param  string $loop
     * @param  string $markup
     */
    private function renderLoop(string $loop, string $markup)
    {
        $this->template = preg_replace('#%%' . $loop . '%%[\w\W\s]*%%' . $loop . '%%#im', $markup, $this->template);
    }
}
