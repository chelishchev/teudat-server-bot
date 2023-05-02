<?php


namespace App\Telegram\Commands;


use App\Models\User;
use App\Models\UserUsage;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class HelpCommand extends UserCommand
{
    protected $name = 'help';
    protected $description = '';
    protected $usage = '/help';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        /** @var User $user */
        $user = User::findByMessage($message);
        if ($user) {
            UserUsage::track($user->id, $this->getName());
        }


        return $this->replyToChat(
            $this->getHelpMessage(),
            [
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]
        );
    }

    protected function getHelpMessage(): string
    {
        return <<<MARKDOWN
Как пользоваться сервисом:\n
1\. Для начала в телеграм\-боте *MyVisit Rega Bot* нужно ввести данные, которые потребуются для генерации уникального кода доступа\. Для этого нажмите /fill\. Если хотите удалить свои данные, нажмите /erase\n
2\. Затем нужно установить расширение [MyVisit Rega Helper](https://chrome.google.com/webstore/detail/myvisit-rega-helper/ookhbjeobapamalmbabaipkobeedjbag)\.\n
3\. Далее на странице настроек расширения *MyVisit Rega Helper* необходимо ввести ваш уникальный код и нажать кнопку "Сохранить"\.\n
На странице отобразятся ваши данные – Имя, номер ТЗ и номер телефона\.\n
4\. После этого необходимо перейти на сайт [myVisit](https://piba.myvisit.com/#!/home/provider/56), ввести капчу и нажать кнопку "Отправить"\.\n
5\. На ваш номер телефона придет смс с кодом, который нужно ввести на сайте [myVisit](https://piba.myvisit.com/#!/home/provider/56) для авторизации\.\n
6\. После авторизации вы автоматически перейдете в раздел [Департамент народонаселения и иммиграции](https://piba.myvisit.com/#!/home/provider/56)\.\n
7\. Расширение автоматически заполнит ваши данные \(введет ID и номер телефона\)\.\n
8\. На третьем шаге начнется поиск свободных слотов во всех хороших отделениях, с паузами в 3\-4 минуты\.\n
9\. Если расширение найдет свободный слот в ближайшие 18 дней, вам придет уведомление в телеграм\-боте *MyVisit Rega Bot*\.\n
10\. Сайт [myVisit](https://piba.myvisit.com/#!/home/provider/56), к сожалению, иногда может быть частично недоступен\. В этом случае расширение ничего не может сделать и отправит уведомление в телеграм\-бот, о том что нужно подождать 😥\.\n
11\. Конечно, когда вы получите уведомление о свободном слоте, будьте максимально шустры, мы в вас верим\! 💪\n
12\. После того как запишитесь, [отблагодарите нас за работу и поддержите проект ❤️️ тут](https://ko-fi.com/myvisit)\.

Нажмите /fill и введите данные о себе для генерации уникального кода доступа\n
MARKDOWN;
    }
}
