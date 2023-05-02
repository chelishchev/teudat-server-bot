<?php

namespace App\Models;

use App\Telegram\Markdown;
use Illuminate\Contracts\Support\Jsonable;
use Longman\TelegramBot\Entities\Message;

class TelegramUserData implements Jsonable
{
    public function __construct(
        protected int     $id,
        protected string  $firstName,
        protected ?string $lastName,
        protected ?string $username,
        protected string  $languageCode,
        protected bool    $isPremium = false,
    )
    {
    }

    public static function buildByMessage(Message $message): static
    {
        return new static(
            $message->getFrom()->getId(),
            $message->getFrom()->getFirstName(),
            $message->getFrom()->getLastName(),
            $message->getFrom()->getUsername(),
            $message->getFrom()->getLanguageCode(),
            $message->getFrom()->getIsPremium() ?? false,
        );
    }

    public function getFullName(): string
    {
        $names = [
            $this->firstName,
            $this->lastName,
        ];

        return implode(' ', array_filter($names)) ?: $this->username;
    }

    public function getMarkdownName(): string
    {
        return Markdown::escapeText($this->getFullName());
    }

    public function getMarkdownLink(): string
    {
        $escapedName = Markdown::escapeText($this->getFullName());
        $username = $this->username;
        $link = $this->getLink();
        if ($username) {
            return "[{$escapedName}]({$link})";
        }

        return "[{$escapedName}]({$link})";
    }

    public function getLink(): string
    {
        $username = $this->username;
        if ($username) {
            //tg://user?id={$username} - на айфоне идёт попап

            return "https://t.me/{$username}";
        }

        return "tg://resolve?id={$this->getId()}";
    }

    public static function fromJson($jsonString): static
    {
        $decode = json_decode($jsonString, true);

        return new static(
            $decode['id'],
            $decode['first_name'],
            $decode['last_name'] ?? null,
            $decode['username'] ?? null,
            $decode['language_code'],
            $decode['is_premium'] ?? false,
        );
    }

    public function toJson($options = 0): string
    {
        return json_encode([
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'username' => $this->username,
            'language_code' => $this->languageCode,
            'is_premium' => $this->isPremium,
        ], $options);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
