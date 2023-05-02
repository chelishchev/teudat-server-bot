<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAppointment;
use App\Models\UserUsage;
use App\Telegram\Markdown;

class EventsController extends ApiBaseController
{
    public function notifySubscribers(): array
    {
        set_time_limit(0);

        $date = $this->request->input('data.date');
        $department = $this->request->input('data.department', []);

        $text = $this->buildMessageForCloseDate($date, $department);
        if (!$text)
        {
            return [];
        }
        if (!\in_array($department['serviceId'], $this->getGoodDepartmentIds()))
        {
            return [];
        }

        $users = User::where('was_token_used', '=', 1)->get();
        $skipNotify = [];
        foreach ($users as $user)
        {
            if (\in_array($user->id, $skipNotify))
            {
                continue;
            }
            $response = \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $user->getTelegramData()->getId(),
                'text' => $text,
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);
            usleep(300000);
        }

        return [];
    }

    protected function getGoodDepartmentIds(): array
    {
        return [
			2161,
			2243,
			2155,
			2099,
			2163,
			2165,
			2095,
			2153,
			2113,
			2245,
			2167,
			2110,
			2150,
			2146,
			2215,
			2159,
			2219,
			2235, //Zfat
			2211, //Akko
			2247, //Eilat
			2217, //Ashkelon
			2196, //Beersheva
			2198, //Dimona
			2239, //Karmiel
		];
    }

    public function notify(string $reason = null): array
    {
        $user = $this->retrieveUserByBearerHeader();
        $reason = $reason ?: $this->request->get('reason');
        if (!$reason) {
            abort(400, 'Reason is empty');
        }

        $text = match ($reason) {
            'enterSmsCode' => "Скоро придёт смс, введите код в браузере на сайте *myVisit*\n\nПосле авторизации вы автоматически перейдете в раздел [Департамент народонаселения и иммиграции](https://piba.myvisit.com/#!/home/provider/56), и расширение начнет поиск слотов 🤞\n\nКогда появится список слотов, выберете подходящий вам\. Откроется новая вкладка\. Выберете нужную услугу, дату и время ☝️",
            'reloadPage' => "Истекла сессия, перезагрузите страницу с *myVisit* в браузере",
            'blockedPage' => "На *myVisit* нагрузка\. Попробуйте позже через 30\-40 минут\. К сожалению, мы не можем ускорить процесс, это вопрос к *myVisit* 😥",
            'closeDate' => $this->buildMessageForCloseDate(),
            'tokenSaved' => "Отлично\! Код доступа на странице расширения *MyVisit Rega Helper* успешно введен 👌\n\nПерейдите на сайт [myVisit](https://piba.myvisit.com), введите капчу и нажмите кнопку \"Отправить\" ",
            'appointmentGot' => $this->buildMessageForAppointmentGot($user),
        };

        if ($this->isInputDataSafe())
        {
            UserUsage::track($user->id, $reason, $this->request->input('data') ?: []);
        }
        if ($reason === 'blockedPage')
        {
            return [];
        }

        if ($text) {
            $response = \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $user->getTelegramData()->getId(),
                'text' => $text,
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);

            if ($reason === 'appointmentGot')
            {
                $response = \Longman\TelegramBot\Request::sendMessage([
                    'chat_id' => $user->getTelegramData()->getId(),
                    'text' => $this->buildMessageForSayThanks(),
                    'parse_mode' => 'MarkdownV2',
                    'disable_web_page_preview' => true,
                ]);
            }
        }

        return [];
    }

    protected function isInputDataSafe(): bool
    {
        $inputData = $this->request->input('data');
        if (empty($inputData))
        {
            return true;
        }

        return strlen(serialize($inputData)) < 1300;
    }

    protected function buildMessageForSayThanks(): string
    {
        $text = <<<TEXT
Поздравляем вас с записью в МВД! 🤝

Мы будем очень благодарны, если вы оставите нам пару слов обратной связи: что сработало хорошо, что не очень, что хотелось бы поправить/изменить 💡 Это можно сделать написав @ivan2divan.
Ваши замечания помогут нам сделать сервис лучше для других пользователей 🫶

Также мы будет очень рады, если вы :link – мы с большим удовольствием выпьем чашечку кофе ☕️
Кроме этого средства пойдут на оплату сервера 💻, с помощью которого работает MyVisit Rega Helper.
Спасибо!
TEXT;

        return Markdown::escapeText($text, [
            ':link' => "[" . Markdown::escapeText('поблагодарите нас за работу') . "](https://ko-fi.com/myvisit)",
        ]);
    }

    protected function buildMessageForAppointmentGot(User $user)
    {
        $date = $this->request->input('data.date');
        $department = $this->request->input('data.department', []);

        if (!is_array($department) || empty($department['name']) || empty($department['serviceId'])) {
            return '';
        }
        if (!$date) {
            return '';
        }

        $userAppointment = new UserAppointment();
        $userAppointment->user_id = $user->id;
        $inputData = $this->request->input('data');
        $userAppointment->appointment_data = $inputData;
        if ($this->isInputDataSafe())
        {
            $userAppointment->save();
        }

        $escapedDate = Markdown::escapeText($date);

        return Markdown::escapeText("⚡⚡⚡ Ура! Мазаль тов 🥳🥳🥳\nПолучилось записаться в :name, :date. Мы очень рады за вас!", [
            ':date' => "*{$escapedDate}*",
            ':name' => "*{$department['name']}*",
        ]);
    }

    protected function buildMessageForCloseDate($date = null, $department = null): string
    {
        $date = $date ?: $this->request->input('data.date');
        $department = $department ?: $this->request->input('data.department', []);

        if (!is_array($department) || empty($department['name']) || empty($department['serviceId'])) {
            return '';
        }
        if (!$date) {
            return '';
        }
        $escapedDate = Markdown::escapeText($date);
        $escapedDepartment = Markdown::escapeText($department['name']);
        $link = "https://piba.myvisit.com/#!/home/provider/56?d={$department['serviceId']}";

        return Markdown::escapeText("⚡⚡⚡ Есть близкая свободная дата :date в :name, бегом на :link\n\n❗Обязательно откройте :link2 в Chrome, где вы установили расширение, тогда вы максимально быстро сможете бронировать слот.", [
            ':date' => "*{$escapedDate}*",
            ':name' => "*{$escapedDepartment}*",
            ':link2' => "[ссылку]({$link})",
            ':link' => "[MyVisit\!]({$link})",
        ]);
    }
}
