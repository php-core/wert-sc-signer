<?php

declare(strict_types=1);

namespace PHPCore\WertScSigner\Tests\Laravel;

use PHPCore\WertScSigner\Laravel\CredentialManager;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class CredentialManagerTest extends TestCase
{
    private const TEST_CREDENTIALS = [
        'default' => 'key_default_123456789abcdef123456789abcdef123456789abcdef123456789abc',
        'production' => 'key_prod_123456789abcdef123456789abcdef123456789abcdef123456789abcd',
        'staging' => 'key_stage_123456789abcdef123456789abcdef123456789abcdef123456789abcd',
    ];

    public function testConstructorSetsCredentials(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->assertEquals('key_default_123456789abcdef123456789abcdef123456789abcdef123456789abc', $manager->get('default'));
        $this->assertEquals('key_prod_123456789abcdef123456789abcdef123456789abcdef123456789abcd', $manager->get('production'));
    }

    public function testGetReturnsDefaultWhenNoNameProvided(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'production');

        $this->assertEquals('key_prod_123456789abcdef123456789abcdef123456789abcdef123456789abcd', $manager->get());
    }

    public function testGetReturnsSpecificCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->assertEquals('key_stage_123456789abcdef123456789abcdef123456789abcdef123456789abcd', $manager->get('staging'));
    }

    public function testGetThrowsExceptionForNonExistentCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Credential 'nonexistent' is not configured");

        $manager->get('nonexistent');
    }

    public function testHasReturnsTrueForExistingCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->assertTrue($manager->has('default'));
        $this->assertTrue($manager->has('production'));
        $this->assertTrue($manager->has('staging'));
    }

    public function testHasReturnsFalseForNonExistentCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->assertFalse($manager->has('nonexistent'));
    }

    public function testGetCredentialNamesReturnsAllKeys(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $names = $manager->getCredentialNames();

        $this->assertCount(3, $names);
        $this->assertContains('default', $names);
        $this->assertContains('production', $names);
        $this->assertContains('staging', $names);
    }

    public function testGetDefaultCredentialNameReturnsCorrectName(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'production');

        $this->assertEquals('production', $manager->getDefaultCredentialName());
    }

    public function testSetAddsNewCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->assertFalse($manager->has('new_credential'));

        $manager->set('new_credential', 'new_key_123');

        $this->assertTrue($manager->has('new_credential'));
        $this->assertEquals('new_key_123', $manager->get('new_credential'));
    }

    public function testSetUpdatesExistingCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $manager->set('default', 'updated_key_456');

        $this->assertEquals('updated_key_456', $manager->get('default'));
    }

    public function testSetCanSetNullValue(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $manager->set('nullable', null);

        $this->assertTrue($manager->has('nullable'));
        $this->assertNull($manager->get('nullable'));
    }

    public function testRemoveDeletesCredential(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        $this->assertTrue($manager->has('staging'));

        $manager->remove('staging');

        $this->assertFalse($manager->has('staging'));
    }

    public function testRemoveNonExistentCredentialDoesNotThrow(): void
    {
        $manager = new CredentialManager(self::TEST_CREDENTIALS, 'default');

        // Should not throw an exception
        $manager->remove('nonexistent');

        $this->assertFalse($manager->has('nonexistent'));
    }

    public function testEmptyCredentialsArray(): void
    {
        $manager = new CredentialManager([], 'default');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Credential 'default' is not configured");

        $manager->get();
    }

    public function testGetWithNullValuesInCredentials(): void
    {
        $credentials = [
            'default' => null,
            'production' => 'some_key',
        ];

        $manager = new CredentialManager($credentials, 'default');

        $this->assertNull($manager->get('default'));
        $this->assertEquals('some_key', $manager->get('production'));
    }
}
