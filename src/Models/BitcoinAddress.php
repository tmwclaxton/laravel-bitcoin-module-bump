<?php

namespace Mollsoft\LaravelBitcoinModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mollsoft\LaravelBitcoinModule\Casts\DecimalCast;
use Mollsoft\LaravelBitcoinModule\Enums\AddressType;

class BitcoinAddress extends Model
{
    protected $fillable = [
        'wallet_id',
        'address',
        'type',
        'title',
        'sync_at',
        'balance',
        'unconfirmed_balance'
    ];

    protected $casts = [
        'type' => AddressType::class,
        'sync_at' => 'datetime',
        'balance' => DecimalCast::class,
        'unconfirmed_balance' => DecimalCast::class,
    ];

    public function wallet(): BelongsTo
    {
        /** @var class-string<BitcoinWallet> $model */
        $model = config('bitcoin.models.wallet');

        return $this->belongsTo($model, 'wallet_id', 'id');
    }

    public function transactions(): HasMany
    {
        /** @var class-string<BitcoinTransaction> $model */
        $model = config('bitcoin.models.transaction');

        return $this->hasMany($model, 'address_id', 'id');
    }
}
