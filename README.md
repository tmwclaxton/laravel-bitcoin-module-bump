![Pest Laravel Expectations](https://banners.beyondco.de/Bitcoin.png?theme=light&packageManager=composer+require&packageName=mollsoft%2Flaravel-bitcoin-module&pattern=architect&style=style_1&description=Working+with+cryptocurrency+Bitcoin&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

<a href="https://packagist.org/packages/mollsoft/laravel-bitcoin-module" target="_blank">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/packagist/v/mollsoft/laravel-bitcoin-module.svg?style=flat&cacheSeconds=3600" alt="Latest Version on Packagist">
</a>

<a href="https://www.php.net">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/badge/php-%3E=8.2-brightgreen.svg?maxAge=2592000" alt="Php Version">
</a>

<a href="https://laravel.com/">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/badge/laravel-%3E=10-red.svg?maxAge=2592000" alt="Php Version">
</a>

<a href="https://packagist.org/packages/mollsoft/laravel-bitcoin-module" target="_blank">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/packagist/dt/mollsoft/laravel-bitcoin-module.svg?style=flat&cacheSeconds=3600" alt="Total Downloads">
</a>

<a href="https://mollsoft.com"><img alt="Website" src="https://img.shields.io/badge/Website-https://mollsoft.com-black"></a>
<a href="https://t.me/mollsoft"><img alt="Telegram" src="https://img.shields.io/badge/Telegram-@mollsoft-blue"></a>

---

**Laravel Bitcoin Module** is a Laravel package for work with cryptocurrency Bitcoin. You can create descriptor wallets, generate addresses, track current balances, collect transaction history, organize payment acceptance on your website, and automate outgoing transfers.

You can contact me for help in integrating payment acceptance into your project.

## Examples

Create Descriptor Wallet:
```php
$name = 'my-wallet';
$password = 'password for encrypt wallet files';
$title = 'My First Wallet';

$wallet = Bitcoin::createWallet($name, $password, $title);
```

Import Descriptor Wallet using descriptors:
```php
$name = 'my-wallet';
$password = 'password for encrypt wallet files';
$descriptions = json_decode('DESCRIPTORS JSON', true);
$title = 'My First Wallet';

$wallet = Bitcoin::importWallet($name, $descriptions, $password, $title);
```

Create address:
```php
$wallet = BitcoinWallet::firstOrFail();
$title = 'My address title';

$address = Bitcoin::createAddress($wallet, AddressType::BECH32, $title);
```

Validate address:
```php
$address = '....';

$addressType = Bitcoin::validateAddress($address);
if( $addressType === null ) {
    die('Address is not valid!');
} 

var_dump($addressType); // Enum value of AddressType
```


## Install

```bash
> composer require mollsoft/laravel-bitcoin-module
> php artisan vendor:publish --tag=bitcoin-config
> php artisan migrate
```

In file `app/Console/Kernel` in method `schedule(Schedule $schedule)` add
```
$schedule->command('bitcoin:sync')->everyMinute()->runInBackground();
```

In .env file add:
```
BITCOIN_RPC_HOST="127.0.0.1"
BITCOIN_RPC_PORT=8332
BITCOIN_RPC_USERNAME="admin"
BITCOIN_RPC_PASSWORD="admin"
```

## Commands

Scan transactions and update balances:

```bash
> php artisan bitcoin:sync
```

Scan transactions and update balances for wallet:

```bash
> php artisan bitcoin:sync-wallet {wallet_id}
```

## Requirements

The following versions of PHP are supported by this version.

* PHP 8.2 and older
* PHP Extensions: Decimal.