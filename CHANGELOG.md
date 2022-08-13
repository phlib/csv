# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- CHANGELOG.md
- PHP 7 type declarations for method parameters and return values
- Factory for creating instance of `Csv` from a CSV file or a zipped CSV file
### Changed
- Throw `InvalidArgumentException` in construct when provided stream is not
seekable, rather than later in methods which access the stream
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
