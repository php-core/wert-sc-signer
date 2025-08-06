<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array signSmartContractData(array $options, string $privateKey)
 * @method static array getScKeysList()
 */
class WertScSigner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'wert-sc-signer';
    }
}