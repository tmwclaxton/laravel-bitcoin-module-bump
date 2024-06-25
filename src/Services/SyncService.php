<?php

namespace Mollsoft\LaravelBitcoinModule\Services;

use Decimal\Decimal;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Mollsoft\LaravelBitcoinModule\BitcoindRpcApi;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;
use Mollsoft\LaravelBitcoinModule\WebhookHandlers\WebhookHandlerInterface;

class SyncService
{
    protected readonly BitcoindRpcApi $api;
    protected readonly WebhookHandlerInterface $webhookHandler;

    protected array $webHooks = [];

    public function __construct(protected readonly BitcoinWallet $wallet) {
        $this->api = $this->wallet->node->api();

        /** @var class-string<WebhookHandlerInterface> $model */
        $model = config('bitcoin.webhook_handler');
        $this->webhookHandler = App::make($model);
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
        $trustedBalance = isset($getBalances['mine']['trusted']) ? number_format($getBalances['mine']['trusted'], 8, '.', '') : '0.00000000';
        $untrustedPendingBalance = isset($getBalances['mine']['untrusted_pending']) ? number_format($getBalances['mine']['untrusted_pending'], 8, '.', '') : '0.00000000';

        $this->wallet->update([
            'balance' => $trustedBalance,
            'unconfirmed_balance' => $untrustedPendingBalance,
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
                $amount = isset($item['amount']) ? number_format($item['amount'], 8, '.', '') : '0.00000000';
                $address->update([
                    'balance' => $amount,
                    'unconfirmed_balance' => $item['confirmations'] === 0 ? $amount : 0,
                ]);
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
            $amount = isset($item['amount']) ? number_format($item['amount'], 8, '.', '') : '0.00000000';
            $deposit = $address?->deposits()->updateOrCreate([
                'txid' => $item['txid']
            ], [
                'wallet_id' => $this->wallet->id,
                'amount' => $amount,
                'block_height' => $item['blockheight'] ?? null,
                'confirmations' => $item['confirmations'] ?? 0,
                'time_at' => Date::createFromTimestamp($item['time']),
            ]);

            if ($deposit?->wasRecentlyCreated) {
                $this->webHooks[] = compact('address','deposit');
            }
        }

        return $this;
    }

    protected function executeWebhooks(): self
    {
        foreach ($this->webHooks as $item) {
            try {
                $this->webhookHandler->handle($this->wallet, $item['address'], $item['deposit']);
            }
            catch(\Exception $e) {
                Log::error('Bitcoin WebHook for deposit '.$item['deposit']->id.' - '.$e->getMessage());
            }
        }

        return $this;
    }
}
