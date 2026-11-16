<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property string $shortcut
 * @property string $title
 * @property string $body
 * @property bool   $active
 * @property int    $sort_order
 */
class QuickReply extends Model
{
    protected $fillable = ['shortcut', 'title', 'body', 'active', 'sort_order'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
