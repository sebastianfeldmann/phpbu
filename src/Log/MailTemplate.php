<?php
namespace phpbu\App\Log;

class MailTemplate
{
    public static $sBody = 'style="font-family: Arial, Helvetica, sans-serif; ' .
                                  'background-color:#343b43; ' .
                                  'font-size: 15px; ' .
                                  'margin:0; ' .
                                  'padding:0;"';

    public static $sTableHeader =  'style="width:500px; ' .
                                          'font-family: Arial, Helvetica, sans-serif; ' .
                                          'margin:0 auto; ' .
                                          'color:#e6e6e6;" ' .
                                   'align="center" cellpadding="5" cellspacing="0"';

    public static $sTableContent = 'style="width:500px; ' .
                                          'font-family: Arial, Helvetica, sans-serif; ' .
                                          'margin:0 auto;" ' .
                                   'align="center" cellpadding="0" cellspacing="0"';

    public static $sTableBackup = 'style="width:100%; ' .
                                         'font-family: Arial, Helvetica, sans-serif; ' .
                                         'background-color:#e6e6e6; ' .
                                         'margin:0 0 15px; ' .
                                         'border:1px solid #011516;" ' .
                                  'align="center" cellpadding="5" cellspacing="0" width="100%"';

    public static $sTableBackupStatusColumn = 'style="background-color:#%s; border-bottom:1px solid #569558;"';

    public static $sTableBackupStatusText = 'style="float:right;"';

    public static $sRowHead = 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"';

    public static $sRowCheck = 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"';

    public static $sRowCrypt = 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"';

    public static $sRowSync = 'style="border-top: 1px solid #f6f6f6; border-bottom: 1px solid #c9c9c9;"';

    public static $sRowCleanup = 'style="border-top: 1px solid #f6f6f6;"';

    public static $sStats = 'style="color:#e6e6e6;"';

    public static $cStatusOK = '91ff94';

    public static $cStatusWARN = 'ffcc6a';

    public static $cStatusFAIL = 'ff7b7b';


}
