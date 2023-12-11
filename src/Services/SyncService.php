<?php

namespace Mollsoft\LaravelBitcoinModule\Services;

use Decimal\Decimal;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelBitcoinModule\BitcoindRpcApi;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinDeposit;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;

class SyncService
{
    protected readonly BitcoindRpcApi $api;

    /** @var BitcoinDeposit[] */
    protected array $newDeposits = [];

    public function __construct(protected readonly BitcoinWallet $wallet) {
        $this->api = $this->wallet->node->api();
    }

    public function run(): void
    {
        $this
            ->unlockWallet()
            ->walletBalances()
            ->addressesBalances()
            ->syncDeposits()
            ->executeWebhooks();
    }

    protected function unlockWallet(): self
    {
        if ($this->wallet->password) {
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

        $this->wallet
            ->addresses()
            ->update([
                'sync_at' => Date::now(),
                'balance' => 0,
                'unconfirmed_balance' => 0,
            ]);

        if (count($listUnspent) > 0) {
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

    protected function syncDeposits(): self
    {
        $listTransactions = $this->api->request('listtransactions', [
            'count' => 100,
        ], $this->wallet->name);

        foreach ($listTransactions as $item) {
            if( $item['category'] !== 'receive' ) {
                continue;
            }

            $address = $this->wallet->addresses()->whereAddress($item['address'])->first();

            $deposit = $address?->deposits()->updateOrCreate([
                'txid' => $item['txid']
            ], [
                'wallet_id' => $this->wallet->id,
                'amount' => new Decimal((string)$item['amount']),
                'block_height' => $item['blockheight'] ?? null,
                'confirmations' => $item['confirmations'],
                'time_at' => Date::createFromTimestamp($item['time']),
            ]);

            if ($deposit?->wasRecentlyCreated) {
                $this->newDeposits[] = $deposit;
            }
        }

        return $this;
    }

    protected function executeWebhooks(): self
    {
        foreach ($this->newDeposits as $deposit) {
            Artisan::call('bitcoin:webhook', [
                'deposit_id' => $deposit->id,
            ]);
        }

        return $this;
    }
}
