<?php
namespace phpbu\App\Log;

use phpbu\App\Exception;

/**
 * MailTemplate
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.8
 */
class MailTemplate
{
    /**
     * List of available snippets
     *
     * @var array
     */
    private static $snippets;

    /**
     * Return a template snippet.
     *
     * @param  string $snippet
     * @return string
     * @throws \phpbu\App\Exception
     */
    public static function getSnippet($snippet)
    {
        if (null === self::$snippets) {
            self::setDefaultSnippets();
        }
        if (!isset(self::$snippets[$snippet])) {
            throw new Exception('Unknown snippet');
        }
        return self::$snippets[$snippet];
    }

    /**
     * Set the default template snippets.
     */
    public static function setDefaultSnippets()
    {
        self::setSnippets(array(
            'sBody'                    => 'style="font-family: Arial, Helvetica, sans-serif; ' .
                                                 'background-color:#343b43; ' .
                                                 'font-size: 15px; margin:0; ' .
                                                 'padding:0;"',
            'sTableHeader'             => 'style="width:100%; ' .
                                                 'font-family: Arial, Helvetica, sans-serif; ' .
                                                 'margin:0; color:#e6e6e6;" ' .
                                          'align="center" cellpadding="5" cellspacing="0"',
            'sTableError'              => 'style="width:100%; ' .
                                                 'background-color:#e6e6e6; ' .
                                                 'margin:0 auto 15px; ' .
                                                 'border:1px solid #011516;" ' .
                                          'align="center" cellpadding="5" cellspacing="0"',
            'sTableErrorCol'           => 'style="border-top: 1px solid #f6f6f6; ' .
                                                 'border-bottom: 1px solid #c9c9c9;"',
            'sTableContent'            => 'style="width:380px; ' .
                                                 'font-family: Arial, Helvetica, sans-serif; ' .
                                                 'margin:0 auto;" ' .
                                          'align="center" cellpadding="0" cellspacing="0"',
            'sTableContentCol'         => 'style="padding:0 10px;"',
            'sTableStatus'             => 'style="background-color:#%s; ' .
                                                 'width:100%%; ' .
                                                 'margin:0 auto 15px; ' .
                                                 'border:1px solid #011516;" ' .
                                          'align="center" cellpadding="10" cellspacing="0"',
            'sTableStatusHead'         => 'style="margin:0;"',
            'sTableStatusText'         => 'style="font-size:16px;"',
            'sTableBackup'             => 'style="width:100%; font-family: Arial, Helvetica, sans-serif; ' .
                                                 'background-color:#e6e6e6; ' .
                                                 'margin:0 0 15px; ' .
                                                 'border:1px solid #011516;" ' .
                                          'align="center" cellpadding="5" cellspacing="0" width="100%"',
            'sTableBackupStatusColumn' => 'style="background-color:#%s; ' .
                                                 'border-bottom:1px solid #747474;"',
            'sTableBackupStatusText'   => 'style="float:right;"',
            'sRowHead'                 => 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"',
            'sRowCheck'                => 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"',
            'sRowCrypt'                => 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"',
            'sRowSync'                 => 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"',
            'sRowCleanup'              => 'style="border-top: 1px solid #f6f6f6;"',
            'sStats'                   => 'style="color:#e6e6e6;"',
            'cStatusOK'                => '91ff94',
            'cStatusWARN'              => 'ffcc6a',
            'cStatusFAIL'              => 'ff7b7b',
        ));
    }

    /**
     * Snippet setter.
     *
     * @param array $list
     */
    public static function setSnippets(array $list)
    {
        self::$snippets = $list;
    }
}
