<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property array $appointment_data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserAppointment extends Model
{
    use HasFactory;

    protected $casts = [
        'appointment_data' => 'array',
    ];
}
