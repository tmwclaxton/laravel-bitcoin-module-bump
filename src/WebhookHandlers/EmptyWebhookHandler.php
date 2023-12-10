<?php

namespace Mollsoft\LaravelBitcoinModule\WebhookHandlers;

use Illuminate\Support\Facades\Log;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinTransaction;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;

class EmptyWebhookHandler implements WebhookHandlerInterface
{
    public function handle(BitcoinWallet $wallet, BitcoinAddress $address, BitcoinTransaction $transaction): void
    {
        Log::error('Bitcoin Wallet '.$wallet->name.' new transaction '.$transaction->txid.' for address '.$address->address);
    }
}