<?php


namespace App\Telegram\Commands;


use App\Models\User;
use App\Models\UserUsage;
use App\Telegram\Markdown;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class EraseCommand extends UserCommand
{
    protected $name = 'erase';
    protected $description = '';
    protected $usage = '/erase';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        /** @var User $user */
        $user = User::findByMessage($message);
        if (!$user) {
            return $this->replyToChat(
                'You are not registered. Use /start command'
            );
        }
        UserUsage::track($user->id, $this->getName());

        $user->teudat_id = null;
        $user->mobile_phone = null;
        $user->name = null;

        $user->save();


        return $this->replyToChat(
            Markdown::escapeText('Ваши введенные ранее данные (номер телефона, ТЗ, имя) удалены. Вы всегда можете запустить /fill для заполнения снова.'),
            [
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]
        );
    }

}
