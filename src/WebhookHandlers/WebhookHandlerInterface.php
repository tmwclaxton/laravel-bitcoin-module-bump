<?php

namespace Mollsoft\LaravelBitcoinModule\WebhookHandlers;

use Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinDeposit;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;

interface WebhookHandlerInterface
{
    public function handle(BitcoinWallet $wallet, BitcoinAddress $address, BitcoinDeposit $transaction): void;
}