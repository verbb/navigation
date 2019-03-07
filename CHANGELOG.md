# Changelog

## 1.1.7.1 - 2019-03-07

### Fixed
- Fix string being passed to `getNavById()`, no need to be that strict.

## 1.1.7 - 2019-03-07

### Changed
- Improved the field to return the navigation model.

## 1.1.6 - 2019-03-03

### Fixed
- Remove A&M Nav migration from install, where it can produce an error in some circumstances.

## 1.1.5 - 2019-02-27

### Added
- Add `node.target` to return either `_blank` or an empty string if the node should open in a new window.

## 1.1.4 - 2019-02-24

### Added
- Add permissions for navs.
- Support aliases in custom URL.

### Fixed
- Fix node titles’s not propagating correctly for nodes.

## 1.1.3 - 2019-02-11

### Fixed
- Fix error thrown on new installs (missing db column).

## 1.1.2 - 2019-02-10

### Fixed
- Fix node level not being applied on new nodes.
- Fix not being able to see new nodes after all have been removed in the CP.
- Fix sortOrder not being set for new navs.
- Fix `getActiveNode()` reporting back parent as active.
- Allow `getActiveNode` to use query criteria.

## 1.1.1 - 2019-02-09

### Fixed
- Fix migration issue for new installs.

## 1.1.0 - 2019-02-09

### Added
- Add navigation field.
- Added classes to `node.link`.
- Add new window and class indicators to nodes in CP.
- Add indicator of custom title for nodes in the CP.
- Add instructions to nav.
- Add A&M Nav migration.
- Added translatable icon to title.

### Fixed
- Fix error when deleting elements in a multi-site.
- Fix node’s being active when they shouldn’t be (matching URLs too early).
- Refactor multi-site propagation of nodes.
- Fix node-type display issues when dragging node in CP.
- Fix not fetching URL for elements that aren’t localised (assets).

## 1.0.18 - 2018-12-06

### Added
- Added `hasActiveChild` to node, for use when not using the `{% nav %}` twig tag.

### Fixed
- Fix migration issue from 1.0.16.1 to 1.0.17.2.

## 1.0.17.2 - 2018-11-25

### Fixed
- Fixed error in migration.

## 1.0.17.1 - 2018-11-24

### Fixed
- Fix migration from 1.0.17 in multi-site.

## 1.0.17 - 2018-11-23

### Fixed
- Fix some elements not having their elementSiteId set, causing multi-site navs to have no URLs.

## 1.0.16.1 - 2018-11-15

### Fixed
- Fix error thrown from console or queue requests when updating elements (for reals).

## 1.0.16 - 2018-11-15

### Fixed
- Fix error thrown from console or queue requests when updating elements.

## 1.0.15 - 2018-11-13

### Fixed
- Fix SQL errors thrown for new installs.

## 1.0.14 - 2018-11-12

### Fixed
- Fix error thrown on homepages.

## 1.0.13 - 2018-11-11

### Changed
- Massive performance improvements, lowering database queries by 98% and rendering speed to two-thirds the time.
- Added `elementSiteId` for all nodes for better site-specific linked element handling.

## 1.0.12 - 2018-10-03

### Fixed
- Fixed a 404 issue due to incorrect URL when editing a nav's nodes.
- Fixed a multi-site issue where the primary site's nodes were being shown, even if the user didn't have permission to edit those nodes.
- Improved handling of pre-selecting localStorage siteId's (when already selecting a site from an element index).

## 1.0.11 - 2018-09-27

### Added
- Add attributes item to object for `render()` tag.
- Add translation for `enabledForSite` (thanks @Saboteur777).

### Fixed
- Ensure nodes are propagated to newly-created sites (if set to propogate nodes in nav settings).
- Adds support for detecting and updating the site id based on changes made when editing entries. (thanks @lemiwinkz).

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
