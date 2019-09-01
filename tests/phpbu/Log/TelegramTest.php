<?php
namespace phpbu\App\Log;

use phpbu\App\Result;

/**
 * Telegram Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Anatoly Skornyakov <anatoly@skornyakov.net>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class TelegramTest extends \PHPUnit\Framework\TestCase
{
    public function testSetUpOk()
    {
        $telegramLog = new Telegram();
        $telegramLog->setup(
            [
                'bot_id' => 1,
                'bot_token' => 'bot_token_qwert',
                'chat_id' => 12,
            ]
        );

        $this->assertTrue(true, 'no exception should occur');
    }

    public function testSetUpWithoutBotId()
    {
        $this->expectException(\phpbu\App\Exception::class);

        $telegramLog = new Telegram();
        $telegramLog->setup(
            [
                'bot_token' => 'bot_token_qwert',
                'chat_id' => 12,
            ]
        );
    }

    public function testSetUpWithoutBotToken()
    {
        $this->expectException(\phpbu\App\Exception::class);

        $telegramLog = new Telegram();
        $telegramLog->setup(
            [
                'bot_id' => 1,
                'chat_id' => 12,
            ]
        );
    }

    public function testSetUpWithoutChatId()
    {
        $this->expectException(\phpbu\App\Exception::class);

        $telegramLog = new Telegram();
        $telegramLog->setup(
            [
                'bot_token' => 'bot_token_qwert',
                'bot_id' => 1,
            ]
        );
    }

    public function testSetUpDail()
    {
        $this->expectException(\phpbu\App\Exception::class);

        $telegramLog = new Telegram();
        $telegramLog->setup([]);
    }

    public function testGetSubscribedEvents()
    {
        $events = Telegram::getSubscribedEvents();

        $this->assertTrue(array_key_exists('phpbu.app_end', $events));
        $this->assertEquals('onPhpbuEnd', $events['phpbu.app_end']);
    }

    public function testGenerateApiUrl()
    {
        $telegramLog = $telegramLog = $this->getTelegramLogWithPublicMethods();

        $this->assertEquals(
            'https://api.telegram.org/bot1:bot_token_qwert/sendMessage',
            $telegramLog->publicGenerateApiUrl(
                [
                    'bot_id' => 1,
                    'bot_token' => 'bot_token_qwert',
                    'chat_id' => 12,
                ]
            )
        );
    }

    public function testGenerateMessageWithError()
    {
        $telegramLog = $this->getTelegramLogWithPublicMethods();

        $resultMock = $this->createMock(Result::class);

        $resultMock
            ->method('allOk')
            ->willReturn(false)
        ;

        $expectedMessage = [
            'Backup is done. But with errors.',
            'Backups:'
        ];
        $this->assertEquals(
            implode(PHP_EOL, $expectedMessage),
            $telegramLog->publicGenerateMessage($resultMock)
        );
    }

    public function testGenerateMessage()
    {
        $telegramLog = $this->getTelegramLogWithPublicMethods();

        $resultMock = $this->createMock(Result::class);

        $resultMock
            ->method('allOk')
            ->willReturn(true)
        ;

        $expectedMessage = [
            'Backup is done.',
            'Backups:'
        ];
        $this->assertEquals(
            implode(PHP_EOL, $expectedMessage),
            $telegramLog->publicGenerateMessage($resultMock)
        );
    }

    private function getTelegramLogWithPublicMethods()
    {
        return new class extends Telegram {
            public function publicGenerateMessage(Result $result): string
            {
                return $this->generateMessage($result);
            }

            public function publicGenerateApiUrl(array $options): string
            {
                return $this->generateApiUrl($options);
            }
        };
    }
}
