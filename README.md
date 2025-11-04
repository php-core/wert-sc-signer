# WertWidget Smart Contract Signing Helper

## Installation

```bash
composer require php-core/wert-sc-signer
```

## Usage

### Standard PHP

```php
use PHPCore\WertScSigner\WertScSigner;

$signedData = WertScSigner::signSmartContractData($options, $privateKey);
```

### Laravel

The package includes Laravel integration. The service provider and facade will be automatically registered.

1. Publish the configuration file:

```bash
php artisan vendor:publish --provider="PHPCore\WertScSigner\Laravel\WertScSignerServiceProvider" --tag="config"
```

2. Add your Wert private key to your .env file:

```env
WERT_PRIVATE_KEY=your_private_key_here
```

3. Use the facade in your code:

```php
use PHPCore\WertScSigner\Laravel\Facades\WertScSigner;

$dataWithSignature = WertScSigner::signSmartContractData($options);
```

Function **signSmartContractData** returns the given options array with an addition of a "**signature**" property. You can pass the result directly to WertWidget initializer.

#### Using Multiple Credentials

The package supports using multiple API credentials in the same Laravel project. This is useful when you need to:
- Separate credentials by environment (production, staging, etc.)
- Use different credentials for different partners or clients
- Manage multiple Wert accounts

**Configuration:**

Update your `config/wert-sc-signer.php` file:

```php
return [
    'private_key' => env('WERT_PRIVATE_KEY'), // Legacy support

    'credentials' => [
        'default' => env('WERT_PRIVATE_KEY'),
        'production' => env('WERT_PRIVATE_KEY_PRODUCTION'),
        'staging' => env('WERT_PRIVATE_KEY_STAGING'),
        'partner_a' => env('WERT_PRIVATE_KEY_PARTNER_A'),
        'partner_b' => env('WERT_PRIVATE_KEY_PARTNER_B'),
    ],

    'default_credential' => env('WERT_DEFAULT_CREDENTIAL', 'default'),
];
```

Add the corresponding environment variables to your `.env` file:

```env
WERT_PRIVATE_KEY=your_default_key
WERT_PRIVATE_KEY_PRODUCTION=your_production_key
WERT_PRIVATE_KEY_STAGING=your_staging_key
WERT_PRIVATE_KEY_PARTNER_A=partner_a_key
WERT_PRIVATE_KEY_PARTNER_B=partner_b_key
WERT_DEFAULT_CREDENTIAL=default
```

**Usage:**

```php
use PHPCore\WertScSigner\Laravel\Facades\WertScSigner;

// Use the default credential (backward compatible)
$dataWithSignature = WertScSigner::signSmartContractData($options);

// Use a specific credential with withCredential()
$dataWithSignature = WertScSigner::withCredential('production')->sign($options);
$dataWithSignature = WertScSigner::withCredential('partner_a')->sign($options);

// Or pass the credential name to the sign() method
$dataWithSignature = WertScSigner::sign($options, 'staging');
```

**Dynamic Credential Selection:**

You can select credentials dynamically based on your application logic:

```php
// Based on environment
$credential = app()->environment('production') ? 'production' : 'staging';
$dataWithSignature = WertScSigner::withCredential($credential)->sign($options);

// Based on user/tenant
$credential = $user->wert_credential_name ?? 'default';
$dataWithSignature = WertScSigner::withCredential($credential)->sign($options);

// Based on partner
$credential = match($partnerId) {
    'partner-a' => 'partner_a',
    'partner-b' => 'partner_b',
    default => 'default',
};
$dataWithSignature = WertScSigner::withCredential($credential)->sign($options);
```

### Options
| Property             | Required |   Type    | Description                                                                                                                                                        |
|:--------------------|:--------:|:---------:|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **address**         | required | *string*  | User's address that will act as a fallback address if a smart contract can't be executed. In case of fallback, Wert will transfer commodity_amount to this address |
| **commodity**       | required | *string*  | [List of supported currencies](https://docs.wert.io/docs/supported-coins-and-blockchains)                                                                          |
| **network**         | optional | *string*  | [List of supported currencies](https://docs.wert.io/docs/supported-coins-and-blockchains)                                                                          |
| **commodity_amount**| required | *numeric* | An amount of crypto necessary for executing the given smart contract                                                                                               |
| **sc_address**      | required | *string*  | The address of the smart contract                                                                                                                                  |
| **sc_input_data**   | required | *string*  | Data that will be used for smart contract execution, in the hex format                                                                                             |

### Example

```php
$commodity = 'ETH';
$network = 'ethereum';
$options = [
    // required generally:
    "partner_id" => "...",

    // required for signing:
    'address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e', // NFT receipient
    'commodity_id' => strtolower($commodity) . '_' . strtolower($network) . '.sc.ethereum',
    'commodity' => $commodity,
    'commodity_amount' => 1.5,
    'network' => $network,
    'sc_address' => '0x...', // smart contract address
    'sc_input_data' => '...', // input data (learn more: https://docs.wert.io/docs/forming-input-data)

    // ...
];

$privateKey = 'your_private_key_here';
$dataWithSignature = WertScSigner::signSmartContractData($options, $privateKey);
```

### Private key

Was given to you during your registration in the **Wert** system as a partner. If you don't have one, [contact Wert](https://wert.io/for-partners).

## Requirements

- PHP 8.0 or higher
- ext-sodium