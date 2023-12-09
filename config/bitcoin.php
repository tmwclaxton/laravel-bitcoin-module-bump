<?php

return [
    'rpc' => [
        'host' => env('BITCOIN_RPC_HOST', '127.0.0.1'),
        'port' => env('BITCOIN_RPC_PORT', 8332),
        'username' => env('BITCOIN_RPC_USERNAME'),
        'password' => env('BITCOIN_RPC_PASSWORD'),
    ],
    'address_type' => \Mollsoft\LaravelBitcoinModule\Enums\AddressType::BECH32,
    'models' => [
        'wallet' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet::class,
        'address' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress::class,
        'transaction' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinTransaction::class,
    ],
];
