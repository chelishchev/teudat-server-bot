<?php

namespace App\Telegram\Commands;

use App\Models\User;
use App\Telegram\Markdown;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class CallbackqueryCommand extends SystemCommand
{
    protected $name = 'callbackquery';

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */
    public function execute(): ServerResponse
    {
        $callbackQuery = $this->getCallbackQuery();
        $callbackData = $callbackQuery->getData();
        xdebug_break();
        $user = User::findByTelegramId($callbackQuery->getFrom()->getId());
        if (!$user || !is_string($callbackData))
        {
            return Request::emptyResponse();
        }

        if (in_array($callbackData, ['mute', 'unmute'], true))
        {
            $this->muteStep($user, $callbackData);
        }
        elseif (in_array($callbackData, ['recommend_yes', 'recommend_no'], true))
        {
            $this->recommendStep($user, $callbackData);
        }
        elseif (in_array($callbackData, ['donate_yes', 'donate_no'], true))
        {
            $this->donateStep($user, $callbackData);
        }

        return Request::emptyResponse();
    }

    private function muteStep(User $user, string $answer): void
    {
        match ($answer)
        {
            'mute' => $user->mute(),
            'unmute' => $user->unmute(),
        };

        $keyboard = (new InlineKeyboard(
            [
                ['text' => 'Да 👍', 'callback_data' => 'recommend_yes'],
                ['text' => 'Нет', 'callback_data' => 'recommend_no'],
            ]
        ));

        \Longman\TelegramBot\Request::sendMessage([
            'chat_id' => $user->getTelegramData()->getId(),
            'text' => "Порекомендуете ли вы MyVisit Rega Helper своим знакомым?",
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,
        ]);
    }

    private function recommendStep(User $user, string $answer): void
    {
        $keyboard = (new InlineKeyboard(
            [
                ['text' => 'Да 🥳', 'callback_data' => 'donate_yes'],
                ['text' => 'Нет', 'callback_data' => 'donate_no'],
            ]
        ));

        \Longman\TelegramBot\Request::sendMessage([
            'chat_id' => $user->getTelegramData()->getId(),
            'text' => "Хотите поблагодарить нас за нашу работу и поддержать сервис, оставив донат на любую сумму?",
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,
        ]);
    }

    private function donateStep(User $user, string $answer): void
    {
        if ($answer === 'donate_yes')
        {
            \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $user->getTelegramData()->getId(),
                'text' => $this->buildDonateText(),
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);
        }
    }

    private function buildDonateText(): string
    {
        $text = "Поблагодарить можно :int, если у вас есть международная карта. Или :ru, если у вас российская карта.";

        return Markdown::escapeText($text, [
            ':int' => "[" . Markdown::escapeText('здесь') . "](https://ko-fi.com/myvisit)",
            ':ru' => "[" . Markdown::escapeText('здесь') . "](https://pay.cloudtips.ru/p/d41cbbdf)",
        ]);
    }
}
