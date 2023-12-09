<?php

namespace Mollsoft\LaravelBitcoinModule\Services;

use Decimal\Decimal;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelBitcoinModule\BitcoindRpcApi;
use Mollsoft\LaravelBitcoinModule\Enums\TransactionCategory;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;

readonly class SyncService
{
    public function __construct(
        protected BitcoinWallet $wallet,
        protected BitcoindRpcApi $api,
    ) {
    }

    public function run(): void
    {
        $this
            ->unlockWallet()
            ->walletBalances()
            ->addressesBalances()
            ->syncTransactions();
    }

    protected function unlockWallet(): self
    {
        if( $this->wallet->password ) {
            $this->api->request('walletpassphrase', [
                'passphrase' => $this->wallet->password,
                'timeout' => 60,
            ], $this->wallet->name);
        }

        return $this;
    }

    protected function walletBalances(): self
    {
        $getBalances = $this->api->request('getbalances', [], $this->wallet->name);
        $this->wallet->update([
            'balance' => new Decimal((string)$getBalances['mine']['trusted'], 8),
            'unconfirmed_balance' => new Decimal((string)$getBalances['mine']['untrusted_pending'], 8),
            'sync_at' => Date::now(),
        ]);

        return $this;
    }

    protected function addressesBalances(): self
    {
        $listUnspent = $this->api->request('listunspent', ['minconf' => 0], $this->wallet->name);
        if (count($listUnspent) > 0) {
            $this->wallet
                ->addresses()
                ->whereNull('sync_at')
                ->orWhereNot('sync_at', $this->wallet->sync_at)
                ->update([
                    'sync_at' => $this->wallet->sync_at,
                    'balance' => 0,
                    'unconfirmed_balance' => 0,
                ]);

            foreach ($listUnspent as $item) {
                $address = $this->wallet
                    ->addresses()
                    ->whereAddress($item['address'])
                    ->lockForUpdate()
                    ->first();
                $address?->increment(
                    $item['confirmations'] > 0 ? 'balance' : 'unconfirmed_balance',
                    (string)$item['amount']
                );
            }
        }

        return $this;
    }

    protected function syncTransactions(): self
    {
        $listTransactions = $this->api->request('listtransactions', [
            'count' => 100,
        ], $this->wallet->name);

        foreach( $listTransactions as $item ) {
            $address = $this->wallet->addresses()->whereAddress($item['address'])->first();
            $address?->transactions()->updateOrCreate([
                'txid' => $item['txid'],
                'category' => TransactionCategory::from($item['category']),
            ], [
                'wallet_id' => $this->wallet->id,
                'amount' => new Decimal((string)$item['amount']),
                'block_height' => $item['blockheight'] ?? null,
                'time_at' => Date::createFromTimestamp($item['time']),
                'confirmations' => $item['confirmations'],
            ]);
        }

        return $this;
    }
}
