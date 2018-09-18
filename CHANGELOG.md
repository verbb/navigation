# Changelog

## 1.0.11 - 2018-09-18

### Fixed
- Ensure nodes are propagated to newly-created sites (if set to propogate nodes in nav settings).

## 1.0.10 - 2018-09-17

### Fixed
- Trim trailing slash in getActive method when addTrailingSlashesToUrls.
- Add `enabledForSite` functionality to allow nodes to be enabled/disabled per site.
- Fix error that could occur when trying to add a new node on a multi site setup.
- Refactor `render()` variables to be cleaner and prevent `activeClass` error.
- Fix `node.link` not working with the newWindow option set.

## 1.0.9 - 2018-08-28

### Fixed
- Fix missing column for propagateNodes in install (whoops).

## 1.0.8 - 2018-08-27

### Fixed
- Fix migration potentially not firing for propagateNodes.

## 1.0.7 - 2018-08-27

### Fixed
- Fix error thrown when not setting 'Propagate nodes'.

## 1.0.6 - 2018-08-26

### Added
- Added `propagateNodes` nav setting.
- Added `getNavByHandle()`.
- Added `activeClass` to `render()` function.
- Added `getActiveNode()`.

### Changed
- Remove required URL for manual node.

### Fixed
- Fix lack of element registration.
- Fix navigation’s maxLevels not working after changing or adding elements to the nav.
- Improve active state on homepage.

## 1.0.5 - 2018-08-15

### Added
- Added `breadcrumbs()` functionality.
- Hungarian translations added (thanks to @Saboteur777).

### Fixed
- Fix manual links and active state.

## 1.0.4 - 2018-08-06

### Fixed
- Fix parent select not always keeping value after adding a new node.
- Remove leftover test values in manual node settings.
- Fixed an error which could prevent the plugin from installing on PostgreSQL.

## 1.0.3 - 2018-07-27

### Fixed
- Fix being unable to fetch elements that are only in a non-primary site
- Fix homepage being set to active on child page.
- Fix deprecated notice.

## 1.0.2 - 2018-07-18

### Changed
- Modified active class to set the active class based on the current URL used.

### Fixed
- Fix `render()` method not resetting into template mode (thanks @billythekid).
- Fix node parent not being active when child node is.

## 1.0.1 - 2018-07-17

### Fixed
- Fix missing alias name for prefixed tables (thanks @qbasic16).
- Fix nodes saving URL for elements in some cases.

## 1.0.0 - 2018-07-13

- Initial release.
