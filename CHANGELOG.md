# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- Add a hook to allow a listener when the iterator is destroyed.
  Helpful for implementations which needs to clean up temporary files.
### Changed
- Use namespaced exceptions.

## [2.1.0] - 2026-06-03
### Added
- Add a union return type to `fetchLine()` to match the method behaviour.
- Throw a specific exception when a record's line is too long to read.
  Previously, an ambiguous exception was thrown ("more columns than headers", or
  "too many columns"), but the message will now say "line too long". 
### Removed
- Removed support for PHP versions <= v8.0 as they are no longer
  [actively supported](https://php.net/supported-versions.php) by the PHP project.

## [2.0.0] - 2022-08-14
### Added
- CHANGELOG.md
- Add support for PHP v8
- Type declarations have been added to all method parameters and return types
  where possible.
- Factory for creating instance of `Csv` from a CSV file or a zipped CSV file
### Changed
- Throw `InvalidArgumentException` in construct when provided stream is not
seekable, rather than later in methods which access the stream
- **BC break**: Reduce visibility of internal methods and properties. These
  members are not part of the public API. No impact to standard use of this
  package. If an implementation has a use case which needs to override these
  members, please submit a pull request explaining the change.
### Removed
- **BC break**: Removed support for PHP versions <= v7.3 as they are no longer
[actively supported](https://php.net/supported-versions.php) by the PHP project

## [1.0.1] - 2018-03-08
### Fixed
- Add fix for when the buffer starts with a BOM and change the offset to
  ignore. Added additional test to cover the improved functionality.

## [1.0.0] - 2017-02-23
Stable release
### Added
- README.md
- Apply GNU LGPLv3 software licence

## [0.0.1] - 2016-09-01
Development release
