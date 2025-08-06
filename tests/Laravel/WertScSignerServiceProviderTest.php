<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner\Tests\Laravel;

use PHPUnit\Framework\TestCase;
use PHPCore\WertScSigner\WertScSigner;

class WertScSignerServiceProviderTest extends TestCase
{
    private array $validOptions = [
        'address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
        'commodity' => 'ETH',
        'commodity_amount' => '1.5',
        'network' => 'ethereum',
        'sc_address' => '0x88271d333C72e51516B67f5567c728E702b3eeE8',
        'sc_input_data' => '0x23b872dd000000000000000000000000742d35cc6634c0532925a3b844bc454e4438f44e'
    ];

    private string $validPrivateKey = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    public function testServiceProviderExists(): void
    {
        $this->assertTrue(
            class_exists('PHPCore\WertScSigner\Laravel\WertScSignerServiceProvider')
        );
    }

    public function testFacadeExists(): void
    {
        $this->assertTrue(
            class_exists('PHPCore\WertScSigner\Laravel\Facades\WertScSigner')
        );
    }

    public function testConfigFileExists(): void
    {
        $this->assertFileExists(
            __DIR__ . '/../../src/Laravel/config/wert-sc-signer.php'
        );
    }
}