<?php

namespace Mollsoft\LaravelBitcoinModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mollsoft\LaravelBitcoinModule\Enums\TransactionCategory;

class BitcoinTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'address_id',
        'txid',
        'category',
        'amount',
        'block_height',
        'time_at',
        'confirmations',
    ];

    protected $casts = [
        'category' => TransactionCategory::class,
        'time_at' => 'timestamp',
    ];

    public function wallet(): BelongsTo
    {
        /** @var class-string<BitcoinWallet> $model */
        $model = config('bitcoin.models.wallet');

        return $this->belongsTo($model, 'wallet_id');
    }

    public function address(): BelongsTo
    {
        /** @var class-string<BitcoinAddress> $model */
        $model = config('bitcoin.models.address');

        return $this->belongsTo($model, 'address_id');
    }
}
