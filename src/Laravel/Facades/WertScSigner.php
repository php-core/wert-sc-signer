<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array signSmartContractData(array $options, ?string $privateKey = null)
 * @method static array getScKeysList()
 * @method static \PHPCore\WertScSigner\WertScSigner withCredential(string $credentialName)
 * @method static array sign(array $options, ?string $credentialName = null, ?string $privateKey = null)
 *
 * @see \PHPCore\WertScSigner\WertScSigner
 */
class WertScSigner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'wert-sc-signer';
    }
}