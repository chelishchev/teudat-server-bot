<?php


namespace App\Telegram\Commands;


use App\Models\User;
use App\Models\UserUsage;
use App\Telegram\Markdown;
use App\Telegram\View\ShowSavedUserData;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Request;

class FillCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'fill';

    /**
     * @var string
     */
    protected $description = 'Fill data teudat zehut, phone and name';

    /**
     * @var string
     */
    protected $usage = '/fill';

    /**
     * @var string
     */
    protected $version = '0.4.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Conversation Object
     *
     * @var Conversation
     */
    protected $conversation;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Preparing response
        $data = [
            'chat_id' => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        // Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();

        // State machine
        // Every time a step is achieved the state is updated
        switch ($state) {
            case 0:
                if ($text === '') {
                    /** @var User $userModel */
                    $userModel = User::where('telegram_id', $user_id)->first();
                    if ($userModel)
                    {
                        UserUsage::track($userModel->id, $this->getName());
                    }

                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Введите номер Теудат Зеута (9 цифр):';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['teudat'] = $text;
                $text = '';

            // No break!
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Введите номер мобильного телефона (с 972):';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['mobile_phone'] = ltrim($text, '+');
                $text = '';

            // No break!
            case 2:
                if ($text === '') {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    $data['text'] = 'Введите ваше имя (в произвольной форме):';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['name'] = $text;
                $text = '';

                $dataForSaving = [
                    'teudat' => $notes['teudat'],
                    'mobile_phone' => $notes['mobile_phone'],
                    'name' => $notes['name'],
                ];

                $this->conversation->stop();

                return $this->showFilledData($user_id, $dataForSaving);
        }

        return $result;
    }

    protected function showFilledData(int $tgUserId, array $dataForSaving)
    {
        /** @var User $user */
        $user = User::where('telegram_id', $tgUserId)->first();
        $user->teudat_id = $dataForSaving['teudat'];
        $user->mobile_phone = $dataForSaving['mobile_phone'];
        $user->name = $dataForSaving['name'];

        $user->save();

        $extLink = 'https://chrome.google.com/webstore/detail/myvisit-rega-helper/ookhbjeobapamalmbabaipkobeedjbag';
        $showSavedUserData = new ShowSavedUserData($user);
        $linkToExtension = "[установите расширение]({$extLink})";

        $replyText = Markdown::escapeText("Если всё ок, :extension в Chrome и введите ваш уникальный код доступа :token на странице расширения, затем нажмите кнопку \"Сохранить\".\n\n", [
            ':token' => "`{$user->access_token}`",
            ':extension' => $linkToExtension,
        ]);

        $replyText =
            Markdown::escapeText("Отлично 👍, данные сохранены!\n\n") .
            $showSavedUserData->toString() .
            $replyText .
            "\n[Установите расширение Chrome]({$extLink})"
        ;


        return $this->replyToChat(
            $replyText,
            [
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]
        );
    }
}
