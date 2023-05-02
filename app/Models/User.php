<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Longman\TelegramBot\Entities\Message;

/**
 * @property int $id
 * @property int $telegram_id
 * @property int $was_token_used
 * @property string $name
 * @property string $teudat_id
 * @property string $mobile_phone
 * @property string $departments
 * @property string $telegram_data
 * @property string $access_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const TOKEN_LENGTH = 32;
    public const TOKEN_LENGTH_OLD = 22;

    protected TelegramUserData $telegramData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'teudat_id',
        'mobile_phone',
        'telegram_id',
        'was_token_used',
        'telegram_data',
        'departments',
        'access_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
        'was_token_used',
        'telegram_id',
        'telegram_data',
        'departments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departments' => 'array',
    ];

    public function getTelegramData(): TelegramUserData
    {
        if($this->telegram_data && !isset($this->telegramData)) {
            return $this->telegramData = TelegramUserData::fromJson($this->telegram_data);
        }

        return $this->telegramData;
    }

    public function setTelegramData(TelegramUserData $telegramUserData): static
    {
        $this->telegram_id = $telegramUserData->getId();
        $this->telegram_data = $telegramUserData->toJson();
        $this->telegramData = $telegramUserData;

        return $this;
    }

    public static function findByToken(string $token): ?static
    {
        return static::where('access_token', $token)->first();
    }

    public static function findByMessage(Message $message): ?static
    {
        $telegramUserData = TelegramUserData::buildByMessage($message);

        return User::query()->where('telegram_id', $telegramUserData->getId())->first();
    }

    public static function buildFromMessage(Message $message): static
    {
        $telegramUserData = TelegramUserData::buildByMessage($message);

        $user = new static();
        $user->access_token = Str::random(self::TOKEN_LENGTH);
        $user->setTelegramData($telegramUserData);

        return $user;
    }

	public function jsonSerialize(): array
	{
		return [
            'phoneNumber' => $this->mobile_phone,
            'shortMobilePhone' => '0' . substr($this->mobile_phone, 3),
            'idNumber' => $this->teudat_id,
            'name' => $this->name,
        ];
	}

}
