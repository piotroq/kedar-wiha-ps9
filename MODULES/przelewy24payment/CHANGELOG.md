# Changelog

## [1.1.4] -

### Changed
- Prevent setRedirectAfter error on LegacyControllerContextProxy [DV-9755]

## [1.1.3] -

### Changed
- Add translation payload [DV-9755]

## [1.1.2] - 

### Fixed
- Fixed calculator price calculations [DV-9447]
- Added extra-amount to installment calculation validation [DV-9447]

### Changed
- Change TranslatorInterface to be based on the PrestaShop version [DV-9447]


## [1.1.1] - 2025-12-16

### Fixed
- Fixed logs directory path configuration [DV-9231]
- Fixed Smarty modificator html_entity_decode [DV-9231]
- Fixed form widget rendering in admin form theme template [DV-9231]
- Fixed PS-8 disable module error [DV-9231]
- Fixed PS-8 cache module error [DV-9231]

### Changed
- Change translation cards [DV-9231]


## [1.1.0] - 2025-12-09

### Added
- Hash added to session_id for secure navigation between controllers [DV-8932]
- Logging for payment process tracking [DV-8839]

### Changed
- Session_id storage format - new format [DV-8932]
- Migration info now displays only when old module is enabled (not just installed) [DV-8929]
- Optimized number of API requests [DV-8839]
- BLIK lvl-0 is now confirmed even without special notification [DV-8927]
- Replaced material-icons with svg icons [DV-8933]
- Fit Google Pay resolver to new payment method format [DV-8933]

### Fixed
- Migration bug from old Przelewy24 module [DV-8928]

## [1.0.0]

- Initial release
