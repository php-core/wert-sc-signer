# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-11-05

### Added
- Multi-credential support for Laravel integration
- `CredentialManager` class to manage multiple named API credentials
- `withCredential()` fluent interface method for credential selection
- New `sign()` instance method with credential name parameter
- Configuration support for multiple credentials via `credentials` array
- `default_credential` configuration option
- Comprehensive test coverage for multi-credential functionality (42 tests, 100 assertions)
- Documentation and examples for multi-credential usage in README

### Changed
- Laravel service provider now registers `CredentialManager` singleton
- `WertScSigner` constructor now accepts optional `CredentialManager` parameter
- Configuration file structure enhanced with `credentials` and `default_credential` keys

### Maintained
- Full backward compatibility with existing single-credential usage
- All existing tests continue to pass
- Static `signSmartContractData()` method unchanged

## [1.0.0] - 2025-08-06

### Added
- Initial release
- PHP implementation of Wert Smart Contract request signing
- Ed25519 signature generation using sodium extension
- Laravel integration with service provider and facade
- Support for environment-based configuration
- Comprehensive test suite with 100% coverage

### Features
- Signature generation compatible with Wert's API
- Support for all required fields:
  - address
  - commodity
  - commodity_amount
  - network
  - sc_address
  - sc_input_data
- Case-insensitive commodity and network handling
- Flexible private key input (with or without '0x' prefix)
- Support for both string and numeric commodity amounts
- Preservation of extra fields in options

### Developer Experience
- PSR-4 autoloading
- Strong typing with PHP 8.0+ features
- Comprehensive error messages
- Laravel integration for easy framework usage
- MIT License