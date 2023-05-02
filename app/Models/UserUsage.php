<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $command
 * @property array $input_data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserUsage extends Model
{
    use HasFactory;

    protected $casts = [
        'input_data' => 'array',
    ];

    public static function track(int $userId, string $command, array $inputData = []): void
    {
        $userUsage = new self();
        $userUsage->user_id = $userId;
        $userUsage->command = $command;
        $userUsage->input_data = $inputData;
        $userUsage->save();
    }
}
