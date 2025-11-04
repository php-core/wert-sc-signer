<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner;

use PHPCore\WertScSigner\Laravel\CredentialManager;

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
     * The credential manager instance.
     *
     * @var CredentialManager|null
     */
    protected ?CredentialManager $credentialManager = null;

    /**
     * The currently selected credential name.
     *
     * @var string|null
     */
    protected ?string $selectedCredential = null;

    /**
     * Create a new WertScSigner instance.
     *
     * @param CredentialManager|null $credentialManager
     */
    public function __construct(?CredentialManager $credentialManager = null)
    {
        $this->credentialManager = $credentialManager;
    }

    /**
     * @return array<string>
     */
    public static function getScKeysList(): array
    {
        return [...self::SC_KEYS, 'signature'];
    }

    /**
     * Select a credential to use for signing.
     *
     * @param string $credentialName
     * @return self A new instance with the selected credential
     */
    public function withCredential(string $credentialName): self
    {
        $instance = clone $this;
        $instance->selectedCredential = $credentialName;
        return $instance;
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
     * Sign smart contract data (instance method with credential support).
     *
     * @param array{
     *     address: string,
     *     commodity: string,
     *     commodity_amount: numeric-string|int|float,
     *     network: string,
     *     sc_address: string,
     *     sc_input_data: string
     * } $options
     * @param string|null $credentialName The credential name to use, or null to use selected/default
     * @param string|null $privateKey Direct private key (overrides credential selection)
     * @return array
     * @throws \InvalidArgumentException
     */
    public function sign(array $options, ?string $credentialName = null, ?string $privateKey = null): array
    {
        // If private key is provided directly, use it
        if ($privateKey !== null) {
            return self::signSmartContractData($options, $privateKey);
        }

        // Determine which credential to use
        $credentialToUse = $credentialName ?? $this->selectedCredential;

        // Try to get private key from credential manager
        if ($this->credentialManager !== null) {
            $privateKey = $this->credentialManager->get($credentialToUse);
        }

        // Fall back to static method which handles config fallback
        return self::signSmartContractData($options, $privateKey);
    }

    /**
     * Sign smart contract data (static method for backward compatibility).
     *
     * @param array{
     *     address: string,
     *     commodity: string,
     *     commodity_amount: numeric-string|int|float,
     *     network: string,
     *     sc_address: string,
     *     sc_input_data: string
     * } $options
     * @param string|null $privateKey
     * @return array
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