<?php

return [
    /*
     * Sets the handler to be used when Bitcoin Wallet has a new deposit.
     */
    'webhook_handler' => \Mollsoft\LaravelBitcoinModule\WebhookHandlers\EmptyWebhookHandler::class,

    /*
     * Set address type of generate new addresses.
     */
    'address_type' => \Mollsoft\LaravelBitcoinModule\Enums\AddressType::BECH32,

    /*
     * Set model class for both BitcoinWallet, BitcoinAddress, BitcoinDeposit,
     * to allow more customization.
     *
     * BitcoindRpcApi model must be or extend `Mollsoft\LaravelBitcoinModule\BitcoindRpcApi::class`
     * BitcoinNode model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinNode::class`
     * BitcoinWallet model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet::class`
     * BitcoinAddress model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress::class`
     * BitcoinDeposit model must be or extend `Mollsoft\LaravelBitcoinModule\Models\BitcoinDeposit::class`
     */
    'models' => [
        'rpc_client' => \Mollsoft\LaravelBitcoinModule\BitcoindRpcApi::class,
        'node' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinNode::class,
        'wallet' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet::class,
        'address' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress::class,
        'deposit' => \Mollsoft\LaravelBitcoinModule\Models\BitcoinDeposit::class,
    ],
];
