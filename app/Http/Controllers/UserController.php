<?php

namespace App\Http\Controllers;

class UserController extends ApiBaseController
{
    public function getMySelf(): array
    {
        $user = $this->retrieveUserByBearerHeader();
        if (!$user->was_token_used)
        {
            $user->was_token_used = 1;
            $user->save();
            (new EventsController($this->request))->notify('tokenSaved');
        }

        return [
            'user' => $user,
        ];
    }

    public function saveDepartmentIds(): array
    {
        $user = $this->retrieveUserByBearerHeader();
        $ids = $this->request->input('ids');
        $user->departments = $ids;
        $user->save();

        return is_array($ids) ? $ids : [];
    }
}
