# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## [v1.1.3](https://github.com/zaphyr-org/session/compare/1.1.2...1.1.3) [2025-06-04]

### New:

* Added connection URL (DSN) to database session manager

### Fixed:

* Fixed expiration time in `ArrayHandler::read` method

## [v1.1.2](https://github.com/zaphyr-org/session/compare/1.1.1...1.1.2) [2025-05-25]

### Changed:

* Session directory for file handler is now created automatically if it does not exist
* Updated doctrine/dbal to v4.2, phpstan to v2.1 and phpunit to v10.5

### Fixed:

* Fixed broken doc link in README.md

## [v1.1.1](https://github.com/zaphyr-org/session/compare/1.1.0...1.1.1) [2025-02-10]

### Fixed:

* Removed phpstan errors
* Removed PHP 8.4 deprecations

## [v1.1.0](https://github.com/zaphyr-org/session/compare/1.0.1...1.1.0) [2023-11-07]

### New:

* Added ArrayHandler class
* Added `force` to `addHandler` method in SessionManager class to allow override
* Added `.vscode/` to gitignore file

### Changed:

* Improved unit tests

### Removed:

* Removed `phpstan/phpstan-phpunit` from require-dev in composer.json

### Fixed:

* Garbage collector in FileHandler class now deletes expired session files correctly

## [v1.0.1](https://github.com/zaphyr-org/session/compare/1.0.0...1.0.1) [2023-10-24]

### Changed:

* Updated repository description
* Changed visibility to `protected` for `tearDown` and `setUp` methods in unit tests
* Renamed `unit` to `phpunit` in composer.json scripts section

### Fixed:

* Fixed namespace error in `SessionManagerTest` class
* Removed .dist from phpunit.xml in .gitattributes export-ignore
* Resolved [#1](https://github.com/zaphyr-org/session/issues/1); `SessionManager::session` always returns the same
  instance once resolved

## v1.0.0 [2023-10-02]

### New:

* First stable release version
