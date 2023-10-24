# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## [v1.0.1](https://github.com/zaphyr-org/session/compare/1.0.0...1.0.1) [2023-10-24]

### Changed:
* Updated repository description
* Changed visibility to `protected` for `tearDown` and `setUp` methods in unit tests
* Renamed `unit` to `phpunit` in composer.json scripts section

### Fixed:
* Fixed namespace error in `SessionManagerTest` class
* Removed .dist from phpunit.xml in .gitattributes export-ignore
* Resolved [#1](https://github.com/zaphyr-org/session/issues/1); `SessionManager::session` always returns the same instance once resolved

## v1.0.0 [2023-10-02]

### New:
* First stable release version
