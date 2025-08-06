<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner;

class WertScSigner
{
    private const SC_KEYS = [
        'address',
        'commodity',
        'commodity_amount',
        'network',
        'sc_address',
        'sc_input_data',
    ];

    /**
     * @return array<string>
     */
    public static function getScKeysList(): array
    {
        return [...self::SC_KEYS, 'signature'];
    }

    private static function trimHexPrefix(string $str): string
    {
        if (empty($str)) {
            return $str;
        }

        if (str_starts_with($str, '0x')) {
            return substr($str, 2);
        }

        return $str;
    }

    /**
     * @param array{
     *     address: string,
     *     commodity: string,
     *     commodity_amount: numeric-string|int|float,
     *     network: string,
     *     sc_address: string,
     *     sc_input_data: string
     * } $options
     * @throws \InvalidArgumentException
     */
    /**
     * @throws \InvalidArgumentException
     */
    public static function signSmartContractData(array $options, ?string $privateKey = null): array
    {
        // Only validate required keys
        $missingKeys = array_diff(self::SC_KEYS, array_keys($options));
        if (!empty($missingKeys)) {
            throw new \InvalidArgumentException(
                'All of following keys in options (as first argument) are required for signing: ' . 
                implode(', ', array_map(fn($key) => "\"$key\"", self::SC_KEYS))
            );
        }

        // Try to get private key from config if not provided
        if ($privateKey === null) {
            if (function_exists('config') && ($configKey = config('wert-sc-signer.private_key'))) {
                if (empty($configKey)) {
                    throw new \InvalidArgumentException('Private key must be provided either as argument or in configuration');
                }
                $privateKey = $configKey;
            } else {
                throw new \InvalidArgumentException('Private key must be provided either as argument or in configuration');
            }
        }

        if (empty($privateKey)) {
            throw new \InvalidArgumentException('Private key cannot be empty');
        }

        // Process the data string exactly like TypeScript version
        $dataString = implode("\n", array_map(function ($key) use ($options) {
            $value = $options[$key];
            
            // Handle specific key transformations
            if ($key === 'commodity_amount') {
                $value = is_string($value) ? $value : (string)floatval($value);
            } elseif (in_array($key, ['commodity', 'network'])) {
                $value = strtolower((string)$value);
            } else {
                $value = (string)$value;
            }

            return "{$key}:{$value}";
        }, self::SC_KEYS));

        // Debug the exact data string
        // error_log("Data string: " . $dataString);
        
        // Use raw data string for hashing
        $hash = $dataString;
        
        // Private key preparation
        $privateKeyTrimmed = self::trimHexPrefix($privateKey);
        if (strlen($privateKeyTrimmed) !== 64) {
            throw new \InvalidArgumentException('Private key must be 32 bytes (64 hex characters) long');
        }
        
        try {
            // Convert hex private key to binary (32 bytes)
            $privateKeyBin = @hex2bin($privateKeyTrimmed);
            if ($privateKeyBin === false || strlen($privateKeyBin) !== 32) {
                throw new \InvalidArgumentException('Invalid private key format - must be 64 hex characters (32 bytes)');
            }

            // Generate key pair directly from private key bytes
            $keyPair = sodium_crypto_sign_seed_keypair($privateKeyBin);
            $secretKey = sodium_crypto_sign_secretkey($keyPair);

            // Sign the raw message
            $signature = sodium_crypto_sign_detached($hash, $secretKey);
            
            return [
                ...$options,
                'signature' => bin2hex($signature),
            ];
        } finally {
            if (isset($secretKey)) {
                sodium_memzero($secretKey);
            }
            if (isset($keyPair)) {
                sodium_memzero($keyPair);
            }
            if (isset($privateKeyBin)) {
                sodium_memzero($privateKeyBin);
            }
        }
    }
}