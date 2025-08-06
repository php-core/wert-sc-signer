<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner\Tests;

use PHPCore\WertScSigner\WertScSigner;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class WertScSignerTest extends TestCase
{
    private const VALID_OPTIONS = [
        'address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
        'commodity' => 'ETH',
        'commodity_amount' => 1.5,
        'network' => 'ethereum',
        'sc_address' => '0x88271d333C72e51516B67f5567c728E702b3eeE8',
        'sc_input_data' => '0x23b872dd000000000000000000000000742d35cc6634c0532925a3b844bc454e4438f44e'
    ];

    private const TEST_PRIVATE_KEY = '123456789abcdef123456789abcdef123456789abcdef123456789abcdef1234';

    public function testGetScKeysList(): void
    {
        $expectedKeys = [
            'address',
            'commodity',
            'commodity_amount',
            'network',
            'sc_address',
            'sc_input_data',
            'signature'
        ];

        $this->assertEquals($expectedKeys, WertScSigner::getScKeysList());
    }

    public function testSignSmartContractDataWithValidInput(): void
    {
        $result = WertScSigner::signSmartContractData(self::VALID_OPTIONS, self::TEST_PRIVATE_KEY);

        // Verify all original keys are present
        foreach (self::VALID_OPTIONS as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }

        // Verify signature is present and is a valid hex string
        $this->assertArrayHasKey('signature', $result);
        $this->assertIsString($result['signature']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $result['signature']);
    }

    public function testSignSmartContractDataWithMissingKeys(): void
    {
        $invalidOptions = self::VALID_OPTIONS;
        unset($invalidOptions['address']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All of following keys in options (as first argument) are required for signing');

        WertScSigner::signSmartContractData($invalidOptions, self::TEST_PRIVATE_KEY);
    }

    public function testSignSmartContractDataWithEmptyPrivateKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Private key cannot be empty');

        WertScSigner::signSmartContractData(self::VALID_OPTIONS, '');
    }

    public function testSignSmartContractDataWithUnexpectedKeys(): void
    {
        $optionsWithExtra = self::VALID_OPTIONS;
        $optionsWithExtra['unexpected_key'] = 'value';

        // Should not throw an error
        $result1 = WertScSigner::signSmartContractData($optionsWithExtra, self::TEST_PRIVATE_KEY);

        // Should produce the same signature as without the unexpected key
        $result2 = WertScSigner::signSmartContractData(self::VALID_OPTIONS, self::TEST_PRIVATE_KEY);

        $this->assertEquals($result2['signature'], $result1['signature'], 
            'Signature should be the same regardless of unexpected keys');
        
        // The unexpected key should still be present in the result
        $this->assertArrayHasKey('unexpected_key', $result1);
        $this->assertEquals('value', $result1['unexpected_key']);
    }

    public function testSignSmartContractDataWithNumericCommodityAmount(): void
    {
        $options = self::VALID_OPTIONS;
        $options['commodity_amount'] = 1.5;

        $result = WertScSigner::signSmartContractData($options, self::TEST_PRIVATE_KEY);

        $this->assertArrayHasKey('signature', $result);
        $this->assertEquals('1.5', $result['commodity_amount']);
    }

    public function testSignSmartContractDataCaseNormalizationForSignature(): void
    {
        // Test that different cases produce the same signature
        $options1 = self::VALID_OPTIONS;
        $options1['commodity'] = 'ETH';
        $options1['network'] = 'ETHEREUM';

        $options2 = self::VALID_OPTIONS;
        $options2['commodity'] = 'eth';
        $options2['network'] = 'ethereum';

        $result1 = WertScSigner::signSmartContractData($options1, self::TEST_PRIVATE_KEY);
        $result2 = WertScSigner::signSmartContractData($options2, self::TEST_PRIVATE_KEY);

        // Original case should be preserved in the result
        $this->assertEquals('ETH', $result1['commodity']);
        $this->assertEquals('ETHEREUM', $result1['network']);

        // Signatures should match despite different cases
        $this->assertEquals(
            $result1['signature'],
            $result2['signature'], 
            'Signatures should match regardless of case in commodity and network'
        );
    }

    public function testSignSmartContractDataWithHexPrefixedPrivateKey(): void
    {
        $result1 = WertScSigner::signSmartContractData(self::VALID_OPTIONS, self::TEST_PRIVATE_KEY);
        $result2 = WertScSigner::signSmartContractData(self::VALID_OPTIONS, '0x' . self::TEST_PRIVATE_KEY);

        $this->assertEquals($result1['signature'], $result2['signature']);
    }

    public function testSignatureConsistency(): void
    {
        $signature1 = WertScSigner::signSmartContractData(self::VALID_OPTIONS, self::TEST_PRIVATE_KEY)['signature'];
        $signature2 = WertScSigner::signSmartContractData(self::VALID_OPTIONS, self::TEST_PRIVATE_KEY)['signature'];

        $this->assertEquals($signature1, $signature2, 'Signatures should be consistent for the same input');
    }

    public function testKnownPrivateKeySignature(): void
    {
        $options = [
            'address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e',
            'commodity' => 'ETH',
            'commodity_amount' => '1.5',
            'network' => 'ethereum',
            'sc_address' => '0x88271d333C72e51516B67f5567c728E702b3eeE8',
            'sc_input_data' => '0x23b872dd000000000000000000000000742d35cc6634c0532925a3b844bc454e4438f44e'
        ];
        
        $privateKey = '0x57466afb5491ee372b3b30d82ef7e7a0583c9e36aef0f02435bd164fe172b1d3';
        
        $result = WertScSigner::signSmartContractData($options, $privateKey);
        
        // Test that it's consistent
        $result2 = WertScSigner::signSmartContractData($options, $privateKey);
        $this->assertEquals($result['signature'], $result2['signature']);
        
        // Also test without 0x prefix to ensure handling is correct
        $result3 = WertScSigner::signSmartContractData($options, substr($privateKey, 2));
        $this->assertEquals($result['signature'], $result3['signature']);
    }
}