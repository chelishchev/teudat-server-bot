<?php


namespace App\Telegram\Commands;


use App\Models\User;
use App\Models\UserUsage;
use App\Telegram\Markdown;
use App\Telegram\View\ShowSavedUserData;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class MeCommand extends UserCommand
{

    /** @var string Command name */
    protected $name = 'me';
    /** @var string Command description */
    protected $description = 'My data';
    /** @var string Usage description */
    protected $usage = '/me';
    /** @var string Version */
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

        $showSavedUserData = new ShowSavedUserData($user);
        $replyText =
            "Вот данные, которые будут автоматически подставляться в браузере:\n\n" .
            $showSavedUserData->toString()
        ;

        UserUsage::track($user->id, $this->getName());

        return $this->replyToChat(
            $replyText,
            [
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]
        );
    }
}
