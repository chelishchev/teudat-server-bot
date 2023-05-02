<?php


namespace App\Telegram\Commands;


use App\Models\User;
use App\Models\UserUsage;
use App\Telegram\Markdown;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class StartCommand extends UserCommand
{

    /** @var string Command name */
    protected $name = 'start';
    /** @var string Command description */
    protected $description = 'Start';
    /** @var string Usage description */
    protected $usage = '/start';
    /** @var string Version */
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $deepLinkingParameter = $message->getText(true);

        if ($message->getFrom()->getIsBot()) {
            return $this->replyToChat(
                'Service does not help bots'
            );
        }

        /** @var User $user */
        $user = User::findByMessage($message);
        if (!$user) {
            $user = User::buildFromMessage($message);
            $user->save();
        }

        $replyText =
            "Привет! :user 👋 \n\n" .
            "Сервис состоит из телеграм-бота :botname и расширения для браузера :extname.\n\n" .
            "В телеграм-бот :botname будут приходить оповещения о наличии свободных слотов и пошаговая инструкция. Также бот нужен для генерации уникального кода доступа, который понадобится для взаимодействия :extension1 с сайтом :myvisit.\n\n" .
            ":extension2 позволит вам автоматически проверять отделения МВД на наличие свободных слотов и быстрее вводить данные, необходимые для авторизации на сайте :myvisit.\n\n" .
            "❗:boldВнимание! Наш сервис – это не автозапись. Сервис находит свободные слоты и автоматизирует ввод ваших данных на сайте, но процедура выбора подходящего слота и записи на конкретную дату и время - только в ваших руках :bold 💪\n" .
            "Вы можете нажать /help, чтобы прочитать подробную инструкцию.\n\n" .
            "Для начала необходимы данные, которые мы будем вводить на :myvisit, и которые нужны для генерации уникального кода.\n\n" .
            "Нажмите /fill и введите данные о себе.\n"
        ;
        $replyText = Markdown::escapeText($replyText, [
            ':bold' => "*",
            ':botname' => "*MyVisit Rega Bot*",
            ':extname' => "*MyVisit Rega Helper*",
            ':myvisit' => "*myVisit*",
            ':user' => "*{$user->getTelegramData()->getMarkdownName()}*",
            ':extension1' => "[расширения MyVisit Rega Helper](https://chrome.google.com/webstore/detail/myvisit-rega-helper/ookhbjeobapamalmbabaipkobeedjbag)",
            ':extension2' => "[Расширение для браузера MyVisit Rega Helper](https://chrome.google.com/webstore/detail/myvisit-rega-helper/ookhbjeobapamalmbabaipkobeedjbag)",
        ]);

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
