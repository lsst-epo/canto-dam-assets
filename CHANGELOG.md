# Canto DAM Assets Changelog

All notable changes to this project will be documented in this file.

## 4.0.5 - UNRELEASED
### Added
* Implement `::getStaticHtml()` for proper display of revisions

## 4.0.4 - 2023.09.25
### Added
* Added an icon to the plugin

### Fixed
* Fixed an issue where multiple Matrix/Neo blocks on the same page didn't work, because the raw `fieldId` was being passed in instead of the namespaced field input id

## 4.0.3 - 2023.09.12
### Fixed
* Fixed an issue where the Canto DAM Asset field didn't work inside of Matrix/Neo blocks due to a namespacing issue

## 4.0.2 - 2023.09.11
### Fixed
* Fixed an issue where the appropriate feature classes were not re-applied to the iFrame when multiple differently configured Canto DAM Asset fields are used on the same entry

## 4.0.1 - 2023.09.10
### Added
* Added **Canto Asset Picker Type** field settings that allows you to choose the type of Canto assets that can be selected: Single Image, Multiple Images, or Whole Album

## 4.0.0 - 2023.08.30
### Added
* Initial release
