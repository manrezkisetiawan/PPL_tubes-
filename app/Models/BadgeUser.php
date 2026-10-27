<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Lencana yang telah diraih pengguna (tabel badge_user).
 */
class BadgeUser extends Pivot
{
    protected $table = 'badge_user';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }
}
