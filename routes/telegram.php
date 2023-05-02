<?php

use Illuminate\Support\Facades\Route;

Route::any('/api/telegram/webhook/{token}', static function (\Longman\TelegramBot\Telegram $bot, $token) {
    if ($token !== config('telegram.bot.api_token')) {
        abort(400);
    }

    $bot->handle();
})->middleware('telegram.network')->name('telegram.webhook');
