<?php

namespace Mollsoft\LaravelBitcoinModule;

use Mollsoft\LaravelBitcoinModule\Enums\AddressType;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;

class Bitcoin
{
    public function __construct(protected readonly BitcoindRpcApi $api)
    {
    }

    public function createWallet(string $name, ?string $password = null, ?string $title = null): BitcoinWallet
    {
        $this->api->request('createwallet', [
            'wallet_name' => $name,
            'passphrase' => $password,
            'load_on_startup' => true,
        ]);

        if ($password) {
            $this->api->request('walletpassphrase', [
                'passphrase' => $password,
                'timeout' => 60
            ], $name);
        }

        $descriptors = $this->api->request('listdescriptors', [
            'private' => true,
        ], $name)['descriptors'];

        /** @var class-string<BitcoinWallet> $model */
        $model = config('bitcoin.models.wallet');

        $wallet = $model::create([
            'name' => $name,
            'title' => $title,
            'password' => $password,
            'descriptors' => $descriptors,
        ]);

        $this->createAddress($wallet, null, 'Primary Address');

        return $wallet;
    }

    public function importWallet(
        string $name,
        array $descriptors,
        ?string $password = null,
        ?string $title = null
    ): BitcoinWallet {
        $this->api->request('createwallet', [
            'wallet_name' => $name,
            'passphrase' => $password,
            'blank' => true,
            'load_on_startup' => true,
        ]);

        if ($password) {
            $this->api->request('walletpassphrase', [
                'passphrase' => $password,
                'timeout' => 60
            ], $name);
        }

        $importDescriptors = $this->api->request('importdescriptors', [
            'requests' => $descriptors,
        ], $name);

        foreach( $importDescriptors as $item ) {
            if( !($item['success'] ?? false) ) {
                throw new \Exception('ImportDescriptors '.($item['error']['code'] ?? 0).' - '.($item['error']['message'] ?? ''));
            }
        }

        /** @var class-string<BitcoinWallet> $model */
        $model = config('bitcoin.models.wallet');

        $wallet = $model::create([
            'name' => $name,
            'title' => $title,
            'password' => $password,
            'descriptors' => $descriptors,
        ]);

        $listReceivedByAddress = $this->api->request('listreceivedbyaddress', ['include_empty' => true], $wallet->name);
        foreach ($listReceivedByAddress as $item) {
            $wallet->addresses()->create([
                'address' => $item['address'],
                'type' => $this->validateAddress($item['address']),
            ]);
        }

        if (count($listReceivedByAddress) === 0) {
            $this->createAddress($wallet, null, 'Primary Address');
        }

        return $wallet;
    }

    public function createAddress(
        BitcoinWallet $wallet,
        ?AddressType $type = null,
        ?string $title = null
    ): BitcoinAddress {
        if (!$type) {
            $type = config('bitcoin.address_type', AddressType::BECH32);
        }

        if ($wallet->password) {
            $this->api->request('walletpassphrase', [
                'passphrase' => $wallet->password,
                'timeout' => 60
            ], $wallet->name);
        }

        $data = $this->api->request('getnewaddress', [
            'address_type' => $type->value,
        ], $wallet->name);
        $address = $data['result'];

        return $wallet->addresses()->create([
            'address' => $address,
            'type' => $type,
            'title' => $title,
        ]);
    }

    public function validateAddress(string $address): ?AddressType
    {
        $validateAddress = $this->api->request('validateaddress', [
            'address' => $address
        ]);

        if( !($validateAddress['isvalid'] ?? false) ) {
            return null;
        }

        if( $validateAddress['iswitness'] ?? false ) {
            return ($validateAddress['witness_version'] ?? false) ? AddressType::BECH32M : AddressType::BECH32;
        }
        if( $validateAddress['isscript'] ?? false ) {
            return AddressType::P2SH_SEGWIT;
        }

        return AddressType::LEGACY;
    }

    public function sendAll(BitcoinWallet $wallet, string $address, int|float|null $feeRate = null)
    {
        if ($wallet->password) {
            $this->api->request('walletpassphrase', [
                'passphrase' => $wallet->password,
                'timeout' => 60
            ], $wallet->name);
        }

        return $this->api->request('sendall', [
            'recipients' => [$address],
            'estimate_mode' => $feeRate ? 'unset' : 'economical',
            'fee_rate' => $feeRate,
            'options' => [
                'send_max' => true,
            ]
        ], $wallet->name);
    }
}
