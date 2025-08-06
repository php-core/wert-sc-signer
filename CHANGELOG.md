# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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