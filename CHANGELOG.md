# Canto DAM Assets Changelog

All notable changes to this project will be documented in this file.

## 4.0.12 - UNRELEASED
### Added
* Added a `webhookSecureToken` setting for validating webhooks
* Added a `SyncController` for handling webhooks for changed assets/albums from Canto
* Added `directUrlPreviewPlay` to the GraphQL schema
* Added the `tenantHostName` plugin setting, and pass this down to the Canto Universal Connector JS

### Changed
* Use `directUrlPreview` instead of `directUrlOriginal` which will work for other media types such as videos as well
* Removed the limitation on albums to include only images, so all media types are supported again
* Removed the `retrieveAssetMetadataEndpoint` setting
* Removed the hard-coding of the `tenant` from the JS, instead using the `tenantHostName` plugin setting
* Removed the hard-coding of the `appId` from the JS, instead using the `appId` plugin setting

## 4.0.11 - 2023.12.27
### Fixed
* Added permission to allow non-admins use of the plugin/field modals

## 4.0.10 - 2023.11.25
### Added
* Paginate the requests to the `batch/content` Canto API endpoint in batches of 100 (the API limit per request), so it will work for larger albums
* Switch over to Vite `^5.0.0` & Node `^20.0.0` for the buildchain

### Changed
* Refactored to use `albumSingleCountLoad` when loading albums, for a much larger pagination size of `1000` (was 50)
* Limit what is displayed in album views to just images, since that's all we allow in the field type currently

## 4.0.9 - 2023.11.13
### Added
* Consolidate the field image rendering, and speed up the initial render by having it work without requiring JavaScript

### Fixed
* Fixed an issue where the name displayed under an asset wasn't getting updated when choosing a new assets, only when the entry was saved

## 4.0.8 - 2023.11.12
### Fixed
* Fixed an issue where field instances weren't initialized when opened via Slideout due to the document custom event already having been triggered

## 4.0.7 - 2023.11.10
### Fixed
* Fixed an issue where the JavaScript for the field was executed out of order when deeply embedded in a Neo / Matrix block combination nested field

## 4.0.6 - 2023.11.09
### Added
* Added a plugin migration to migrate existing field data to use the `Schema::TYPE_JSON` column type
* Add the package `daccess1/yii2-json-query-helper` to aid in creating `JSON_CONTAINS()` Query Expressions

### Changed
* Changed the content column types to `Schema::TYPE_JSON` because it's more correct, and we can also use `JSON_CONTAINS()` SQL queries in these columns

### Fixed
* Make sure we camelize the keys if an array is being returned, since we normalize them to be camelized as GraphQL doesn't support spaces or other special characters in the query params
* Work around a Craft bug with custom fields that have JSON column types ([#13916](https://github.com/craftcms/cms/issues/13916))

## 4.0.5 - 2023.10.09
### Added
* Implement `::getStaticHtml()` for proper display of revisions

### Fixed
* Fixed an issue where a single selected Canto asset’s ID wasn’t save properly

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
