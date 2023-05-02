<?php

namespace App\Telegram\View;

use App\Models\User;
use App\Telegram\Markdown;

class ShowSavedUserData
{
    public function __construct(protected User $user)
    {
    }

    public function toString(): string
    {
        $replyText =
            "Теудат Зеут:\n:teudat\n\n" .
            "Мобильный телефон:\n:mobile\n\n" .
            "Код доступа:\n:token\n\n" .
            "Проверьте ваши данные.\nЕсли нашли ошибку, нажмите /fill ещё раз и введите данные заново.\n\n"
        ;

        return Markdown::escapeText($replyText, [
            ':teudat' => "*{$this->user->teudat_id}*",
            ':mobile' => "*{$this->user->mobile_phone}*",
            ':token' => "`{$this->user->access_token}`",
        ]);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
