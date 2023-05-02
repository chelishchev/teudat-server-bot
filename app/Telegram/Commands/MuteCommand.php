<?php


namespace App\Telegram\Commands;


use App\Models\User;
use App\Models\UserUsage;
use App\Telegram\Markdown;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class MuteCommand extends UserCommand
{

    protected $name = 'mute';
    protected $description = '';
    protected $usage = '/mute';
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

        $user->mute_status = User::MUTE_STATUS_ON;
        $user->save();

        return $this->replyToChat(
            text: Markdown::escapeText('Автоматические уведомления о новых слотах отключены 👍.\nВы всегда можете запустить /unmute для включения.'),
            data: [
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]
        );
    }
}
