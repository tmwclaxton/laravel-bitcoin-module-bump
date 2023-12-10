<?php

namespace Mollsoft\LaravelBitcoinModule;

use Mollsoft\LaravelBitcoinModule\Commands\BitcoinSyncCommand;
use Mollsoft\LaravelBitcoinModule\Commands\BitcoinSyncWalletCommand;
use Mollsoft\LaravelBitcoinModule\Commands\BitcoinWebhookCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BitcoinServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('bitcoin')
            ->hasConfigFile()
            ->hasMigrations([
                '2023_12_01_000001_create_bitcoin_wallets_table',
                '2023_12_01_000002_create_bitcoin_addresses_table',
                '2023_12_01_000003_create_bitcoin_transactions_table',
            ])
            ->runsMigrations()
            ->hasCommands(
                BitcoinSyncCommand::class,
                BitcoinSyncWalletCommand::class,
                BitcoinWebhookCommand::class,
            );

        $this->app->singleton(BitcoindRpcApi::class, fn() => new BitcoindRpcApi(
            config('bitcoin.rpc.host'),
            (int)config('bitcoin.rpc.port'),
            config('bitcoin.rpc.username'),
            config('bitcoin.rpc.password')
        ));

        $this->app->singleton(Bitcoin::class);
    }
}