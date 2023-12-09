<?php

namespace Mollsoft\LaravelBitcoinModule\Facades;

use Illuminate\Support\Facades\Facade;

class Bitcoin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mollsoft\LaravelBitcoinModule\Bitcoin::class;
    }
}
