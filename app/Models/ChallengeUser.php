<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Keikutsertaan pengguna pada sebuah tantangan (tabel challenge_user).
 */
class ChallengeUser extends Pivot
{
    public const STATUS_RUNNING = 'berjalan';

    public const STATUS_COMPLETED = 'selesai';

    public const STATUS_FAILED = 'gagal';

    public const STATUS_CANCELLED = 'batal';

    public const STATUSES = [
        self::STATUS_RUNNING => 'Berjalan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_FAILED => 'Gagal',
        self::STATUS_CANCELLED => 'Batal',
    ];

    protected $table = 'challenge_user';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'progress' => 'decimal:2',
            'joined_at' => 'datetime',
        ];
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }
}
