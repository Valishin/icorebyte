<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registra update_ids procesados para garantizar idempotencia
 * (Telegram reintenta si no recibe 200 a tiempo).
 *
 * @property int $id
 * @property int $update_id
 */
class TelegramUpdate extends Model
{
    protected $fillable = ['update_id'];
}
