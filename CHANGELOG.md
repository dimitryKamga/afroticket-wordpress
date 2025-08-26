# Changelog

All notable changes to the AfroTicket WordPress project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-08-26

### Added
- **Automated Deployment System**: Complete webhook integration with GitHub for automatic live site updates
- **Secure SMS Ticket Delivery**: Hash-based secure download URLs with time expiration (72 hours default)
- **Enhanced Phone Input**: 10-digit validation with automatic +1 prefix formatting for SMS
- **Backup System**: Automated deployment backups with 10-backup retention policy
- **Security Compliance**: WordPress options-based credential storage (no hardcoded secrets)
- **Deployment Logging**: Comprehensive logging system for deployment activities and errors

### Changed
- **EventList Template Override**: Custom cart/customer_input.php with enhanced phone validation
- **Child Theme Enhancement**: Extended functions.php with complete SMS integration system
- **Git Repository Structure**: WordPress-optimized .gitignore for versioning only customizations
- **Twilio Integration**: Migrated from hardcoded credentials to secure WordPress options storage

### Fixed
- **SMS System Recovery**: Restored complete SMS functionality after syntax error cleanup
- **Webhook Authentication**: Fixed GitHub signature verification with HMAC-SHA256
- **Phone Number Formatting**: Proper +1 prefix addition for international SMS delivery
- **File Permissions**: Automated permission setting during deployment process

### Security
- **Secret Management**: Removed all hardcoded credentials from version control
- **Hash-based URLs**: SHA256 secure download links to prevent ticket enumeration
- **Rate Limiting**: 5 downloads per IP per hour for ticket security
- **Webhook Security**: HMAC-SHA256 signature verification for GitHub webhooks

## [1.0.0] - 2025-08-25

### Added
- **Initial WordPress Setup**: Base Meup child theme with custom styling
- **EventList Integration**: PDF ticket generation with featured image support
- **WooCommerce Customization**: Removed unnecessary checkout fields (state, country, city, postcode)
- **Custom Taxonomies**: Price, Job, Time, and Space taxonomies for EventList
- **Font Integration**: DM Sans font family with multiple weights
- **jQuery Optimization**: Removed jQuery Migrate console logging

### Changed
- **Theme Structure**: Established child theme architecture for upgrade safety
- **Styling System**: Custom CSS with font-face declarations

---

## Version Guidelines

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** (X.0.0): Breaking changes affecting child theme compatibility
- **MINOR** (0.X.0): New features and enhancements (SMS system, webhook automation)
- **PATCH** (0.0.X): Bug fixes and minor improvements

## Deployment Integration

Changes are automatically deployed to the live site when pushed to the main branch via GitHub webhook automation. Each deployment creates a timestamped backup for rollback capability.