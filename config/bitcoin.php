<?php

return [
    /*
     * Sets accesses for Bitcoind RPC API.
     */
    'rpc' => [
        'host' => env('BITCOIN_RPC_HOST', '127.0.0.1'),
        'port' => env('BITCOIN_RPC_PORT', 8332),
        'username' => env('BITCOIN_RPC_USERNAME'),
        'password' => env('BITCOIN_RPC_PASSWORD'),
    ],

    /*
     * Sets the handler to be used when Bitcoin Wallet has a new deposit.
     */
    'webhook_handler' => \Mollsoft\LaravelBitcoinModule\WebhookHandlers\EmptyWebhookHandler::class,

    /*
     * Set address type of generate new addresses.
     */
    'address_type' => \App\Enums\AddressType::BECH32,

    /*
     * Set model class for both BitcoinWallet, BitcoinAddress, BitcoinTransaction,
     * to allow more customization.
     *
     * BitcoinWallet model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet::class`
     * BitcoinAddress model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress::class`
     * BitcoinTransaction model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinTransaction::class`
     */
    'models' => [
        'wallet' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet::class,
        'address' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress::class,
        'deposit' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinDeposit::class,
    ],
];
