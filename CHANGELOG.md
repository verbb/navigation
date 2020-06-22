# Changelog

## 1.3.20 - 2020-06-22

### Added
- Add `getModalHtml` for custom node types.

### Fixed
- Fix JS errors when there are multiple custom node types.
- Ensure custom node types have their node set, as early as possible.
- Ensure custom node types save the url property.

## 1.3.19 - 2020-06-06

### Fixed
- Fix JS error when editing navigations.

## 1.3.18 - 2020-06-05

### Fixed
- Fix checking to see if Commerce is installed to enable products.

## 1.3.17 - 2020-06-02

### Fixed
- Fix incorrect site being selected when editing a nav.

## 1.3.16 - 2020-05-29

### Fixed
- Fix site dropdown selection not persisting from element indexes

## 1.3.15 - 2020-05-20

### Fixed
- Fix `activeNode` taking into account the suffix for a URL. Active nodes will now return regardless of the defined suffix.
- Fixed deprecation error on `buildNavTree `. (thanks @jaydensmith).

## 1.3.14 - 2020-05-11

### Fixed
- Fix site URL errors on Craft 3.5 beta.

## 1.3.13 - 2020-04-28

### Fixed
- Fix custom URL getting overwritten when selecting elements in custom fields.

## 1.3.12 - 2020-04-18

### Added
- Allow `getActiveNode()` to include option to match against children being active.

## 1.3.11 - 2020-04-16

### Fixed
- Fix logging error `Call to undefined method setFileLogging()`.

## 1.3.10 - 2020-04-15

### Changed
- File logging now checks if the overall Craft app uses file logging.
- Log files now only include `GET` and `POST` additional variables.

## 1.3.9 - 2020-04-14

### Fixed
- Use `getBaseUrl()` for parsing Site node type URLs.
- Fix sort order not persisting when saving navs.
- Fix custom attributes rendering incorrectly when using `node.link`.

## 1.3.8 - 2020-04-02

### Fixed
- Ensure plugin project config is removed when uninstalling.
- Fix incorrect permissions being enforced for new navs.

## 1.3.7 - 2020-03-30

### Changed
- Refactored `getLink()` and pass classes and custom attribute values through `renderObjectTemplate()`. (thanks @jaydensmith).

### Fixed
- Hide nav settings URL if the user doesn’t have permission.
- Fix malformed UTF-8 characters when adding a node.

## 1.3.6 - 2020-02-24

### Changed
- Ensure saving node’s URL is kept raw, and not the generated URL.
- Allow Twig to be used in node’s URLs, so you can use for example `{{ siteUrl('blog') }}` for site-specific URLs.

### Fixed
- Add element site menu to node editor HUD. (thanks @steverowling).
- Fix double escaping of nav titles in vue admin tables. (thanks @steverowling).

## 1.3.5 - 2020-02-12

### Fixed
- Fix potential fatal error when upgrading from previous Craft/plugin versions.

## 1.3.4 - 2020-02-11

### Added
- Add more functionality to node types, now fully-featured!
- Add Site node type, for selecting whole sites, and using their Base URL.

### Fixed
- Fix error for site node types when propagating.
- Bring gql implementation up to speed.
- Fix JS error when adding a manual node.

## 1.3.3 - 2020-02-05

### Fixed
- Fix disabled state incorrectly showing when saving a node.
- Fix Navee migration.

## 1.3.2 - 2020-02-03

### Fixed
- Fix migration issues when other migrations save elements.

## 1.3.1 - 2020-02-01

### Changed
- Revert behaviour of URL generation. No longer enforce the use of Craft’s `url()` function when generating URLs for nodes.

## 1.3.0 - 2020-01-29

### Added
- Craft 3.4 compatibility.

## 1.2.4 - 2020-01-09

### Fixed
- Fix empty URL being overridden.
- Change url to use `siteUrl` instead of `url`.

## 1.2.3 - 2020-01-09

### Fixed
- Fix error thrown when saving a node's element in some cases.

## 1.2.2 - 2020-01-09

### Fixed
- Fix missing `displayName` twig function.
- Fix project config issue with `maxNodes`.

## 1.2.1 - 2020-01-09

### Fixed
- Fix GraphQL issue when querying children.

## 1.2.0 - 2020-01-08

### Added
- Add custom field support for navigation nodes. Add any additional fields to each node! (thanks @jaydensmith).
- Allow swapping of an element in the HUD for a node, once the node has been created.
- Add feature to switch node type for existing nodes. Makes it easy to switch from Entry to Custom URL.
- Add GraphQL support. See [docs](https://verbb.io/craft-plugins/navigation/docs/developers/graphql).
- Add custom node types. The ability to define your own custom type of nodes. See [docs](https://verbb.io/craft-plugins/navigation/docs/developers/extending-elements#node-types).
- Add custom attributes for nodes. Define your own attributes (think `data-scroll`, etc) attached to the anchor tag for each node.
- Add URL suffix for nodes - in case you want to add `#example` or `?some-query=value` to element URLs.
- Add max nodes option for navs to limit the number of nodes in a nav.
- Add separate user permissions for create/edit/delete navs.

### Changed
- Run non-full URLs through Craft's `url()` function. This will help defining relative URLs, so they don't always need to begin with a `/`.

## 1.1.14.1 - 2019-11-27

### Fixed
- Fix pesky debug output!

## 1.1.14 - 2019-11-27

### Added
- Add Navee migration + add panels to settings.
- Add `EVENT_NODE_ACTIVE`.
- Add `disabledElements` to disable certain element from being added to navs.
- Add getAllNavs(). Thanks @lewisjenkins.

### Fixed
- Update permissions to act correctly.
- Fix potential error when deleting nodes.
- Improve save-element checks for elements that have a URL.
- Fix being unable to create new nav with the same handle as a deleted one.

## 1.1.13 - 2019-07-24

### Added
- Add better multi-site handling to A&M nav migration.

### Changed
- Hide the “Parent” select field when the nav’s max levels are 1.
- Update node propagation to use `getSupportedSites()`.

### Fixed
- Fix error when saving a nav in some instances.
- Fix lack of permission enforcement for navs.
- Fix type mismatch error on PostgreSQL. (thanks @boboldehampsink).

## 1.1.12 - 2019-05-15

### Added
- Add `craft.navigation.getNavById()`.
- Add `craft.navigation.getNavByHandle()`.
- Add feedback to A&M nav migration.

### Changed
- Min requirement to Craft 3.1.x.

### Fixed
- Fix A&M migration not using the sites’ language to match nodes on.

## 1.1.11 - 2019-03-19

### Fixed
- Fix not being able to edit nodes with `allowAdminChanges` enabled.
- Fix error when propagating a manual node in a multi-site setup.

## 1.1.10 - 2019-03-17

### Fixed
- Fix schema version check in migration.
- Ensure navs are read-only when `allowAdminChanges` is true.

## 1.1.9.1 - 2019-03-15

### Fixed
- Fix migration issue, caused in some instances.

## 1.1.9 - 2019-03-15

### Added
- Add override notice for settings fields.
- Support for project config.

### Fixed
- Fix linked element URL query to afterPrepare().
- Fix error when deleting nodes.
- Fix Postgres error for querying linked element URLs.

## 1.1.8 - 2019-03-10

### Added
- Add `craft.navigation.tree()`.

### Changed
- Remove `elementSiteId` and refactor linked element’s siteId handling.
- Removes the need to create individual elements for each node when propagating is true.
- Better way to store the linked element’s siteId, via the node’s slug.

### Fixed
- Fix errors when adding multiple site-enabled nodes to a nav.
- Better validation when trying to create a nav with a duplicate handle.

## 1.1.7.3 - 2019-03-07

### Fixed
- Fix migration a little more.

## 1.1.7.2 - 2019-03-07

### Fixed
- Fix navigation field to use handle.
- Add migration for IDs or Nav models saved for content.

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
