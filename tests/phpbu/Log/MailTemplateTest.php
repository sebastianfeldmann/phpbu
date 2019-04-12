<?php
namespace phpbu\App\Log;

/**
 * Mail Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MailTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests MailTemplate::setSnippets
     */
    public function testDefaultSnippets()
    {
        $this->assertEquals('91ff94', MailTemplate::getSnippet('cStatusOK'));
    }

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
     */
    public function testInvalidSnippet()
    {
        $this->expectException('phpbu\App\Exception');
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
