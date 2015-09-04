<?php
namespace phpbu\App\Log;

/**
 * Mail Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MailTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests MailTemplate::setSnippets
     */
    public function testSetSnippets()
    {
        MailTemplate::setSnippets(array('foo' => 'bar'));

        $this->assertEquals('bar', MailTemplate::getSnippet('foo'));
    }

    /**
     * Test MailTemplate::getSnippet
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidSnippet()
    {
        MailTemplate::getSnippet('bar');
    }

    /**
     * Tests MailTemplate::setDefaultSnippets
     */
    public function testSetDefaultSnippets()
    {
        MailTemplate::setDefaultSnippets();
        $this->assertEquals('91ff94', MailTemplate::getSnippet('cStatusOK'));
    }
}
