<?php

namespace PHPCore\WertScSigner\Laravel;

use InvalidArgumentException;

/**
 * Manages multiple Wert API credentials for use in a Laravel application.
 */
class CredentialManager
{
    /**
     * The array of configured credentials.
     *
     * @var array<string, string|null>
     */
    protected array $credentials;

    /**
     * The name of the default credential to use.
     *
     * @var string
     */
    protected string $defaultCredential;

    /**
     * Create a new credential manager instance.
     *
     * @param array<string, string|null> $credentials
     * @param string $defaultCredential
     */
    public function __construct(array $credentials = [], string $defaultCredential = 'default')
    {
        $this->credentials = $credentials;
        $this->defaultCredential = $defaultCredential;
    }

    /**
     * Get a credential by name.
     *
     * @param string|null $name The credential name, or null to use the default
     * @return string|null The private key, or null if not found
     * @throws InvalidArgumentException If the credential name doesn't exist
     */
    public function get(?string $name = null): ?string
    {
        $name = $name ?? $this->defaultCredential;

        if (!array_key_exists($name, $this->credentials)) {
            throw new InvalidArgumentException("Credential '{$name}' is not configured");
        }

        return $this->credentials[$name];
    }

    /**
     * Check if a credential exists.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->credentials);
    }

    /**
     * Get all configured credential names.
     *
     * @return array<string>
     */
    public function getCredentialNames(): array
    {
        return array_keys($this->credentials);
    }

    /**
     * Get the default credential name.
     *
     * @return string
     */
    public function getDefaultCredentialName(): string
    {
        return $this->defaultCredential;
    }

    /**
     * Add or update a credential at runtime.
     *
     * @param string $name
     * @param string|null $privateKey
     * @return void
     */
    public function set(string $name, ?string $privateKey): void
    {
        $this->credentials[$name] = $privateKey;
    }

    /**
     * Remove a credential.
     *
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->credentials[$name]);
    }
}
