<?php

declare(strict_types=1);

namespace phpbu\App\Log;

use GuzzleHttp\Client;
use phpbu\App\Exception;
use phpbu\App\Event;
use phpbu\App\Listener;
use phpbu\App\Result;

/**
 * Class Telegram
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Anatoly Skornyakov <anatoly@skornyakov.net>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class Telegram  implements Listener, Logger
{
    private const URL = 'https://api.telegram.org/bot%d:%s/sendMessage';

    private const CHAT_ID_OPTION_INDEX = 'chat_id';

    private const BOT_ID_OPTION_INDEX = 'bot_id';

    private const BOT_TOKEN_OPTION_INDEX = 'bot_token';

    private const REQUIRED_OPTIONS = [
        self::CHAT_ID_OPTION_INDEX,
        self::BOT_ID_OPTION_INDEX,
        self::BOT_TOKEN_OPTION_INDEX,
    ];

    /**
     * @var int
     */
    private $chatId;

    /**
     * @var Client
     */
    private $client;

    /**
     * Setup the logger.
     *
     * @param array $options
     *
     * @throws Exception
     */
    public function setup(array $options)
    {
        foreach (self::REQUIRED_OPTIONS as $index) {
            if (empty($options[$index])) {
                throw new Exception(
                    sprintf(
                        'Telegram Logger: Not set %s',
                        $index
                    )
                );
            }
        }

        $this->chatId = $options[self::CHAT_ID_OPTION_INDEX];

        $this->client = new Client(
            [
                'base_uri' => $this->generateApiUrl($options),
            ]
        );
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function generateApiUrl(array $options): string
    {
        return sprintf(
            self::URL,
            $options[self::BOT_ID_OPTION_INDEX],
            $options[self::BOT_TOKEN_OPTION_INDEX]
        );
    }

    /**
     * @param string $message
     */
    private function sendMessage(string $message): void
    {
        $this->client->get(
            '',
            [
                'query' => [
                    'text' => $message,
                    'chat_id' => $this->chatId,
                ],
            ]
        );
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Event\App\End::NAME => 'onPhpbuEnd',
        ];
    }

    /**
     * @param Event\App\End $event
     */
    public function onPhpbuEnd(Event\App\End $event): void
    {
        $this->sendMessage(
            $this->generateMessage($event->getResult())
        );
    }

    /**
     * @param Result $result
     *
     * @return string
     */
    protected function generateMessage(Result $result): string
    {
        $messageLines = [
            'Backup is done.' . ($result->allOk() ? '' : ' But with errors.'),
            'Backups:',
        ];

        /** @var \phpbu\App\Result\Backup $backup */
        foreach ($result->getBackups() as $backup) {
            $messageLines[] = sprintf(
                '* %s %s',
                ($backup->allOk() ? '✅' : '‼️'),
                $backup->getName()
            );
        }

        return implode(PHP_EOL, $messageLines);
    }
}
