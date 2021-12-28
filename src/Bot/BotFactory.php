<?php

namespace App\Bot;

class BotFactory
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function __invoke(): \BotMan\BotMan\BotMan
    {
        $config = [
            "telegram" => [
                "token" => $this->token
            ]
        ];

        \BotMan\BotMan\Drivers\DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

        return \BotMan\BotMan\BotManFactory::create($config);
    }
}
