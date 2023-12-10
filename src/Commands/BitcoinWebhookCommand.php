<?php

namespace Mollsoft\LaravelBitcoinModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinTransaction;
use Mollsoft\LaravelBitcoinModule\WebhookHandlers\WebhookHandlerInterface;

class BitcoinWebhookCommand extends Command
{
    protected $signature = 'bitcoin:webhook {transaction_id}';

    protected $description = 'Bitcoin webhook handler';

    public function handle(): void
    {
        /** @var class-string<BitcoinTransaction> $model */
        $model = config('bitcoin.models.transaction');
        $transaction = $model::with(['wallet', 'address'])->findOrFail($this->argument('transaction_id'));

        /** @var class-string<WebhookHandlerInterface> $model */
        $model = config('bitcoin.webhook_handler');

        /** @var WebhookHandlerInterface $handler */
        $handler = App::make($model);

        $handler->handle($transaction->wallet, $transaction->address, $transaction);

        $this->info('Webhook successfully execute!');
    }
}
