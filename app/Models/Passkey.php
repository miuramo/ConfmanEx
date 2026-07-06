<?php

namespace App\Models;

use Laravel\Passkeys\Passkey as BasePasskey;

/**
 * @property int $counter
 * @property array<string>|null $transports
 * @property bool $backed_up
 * @property string $device_type
 */
class Passkey extends BasePasskey
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'credential_id',
        'credential',
        'counter',
        'transports',
        'backed_up',
        'device_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'transports' => 'array',
            'backed_up'  => 'boolean',
        ]);
    }
}
