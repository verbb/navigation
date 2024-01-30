# Changelog

## 2.0.25 - 2024-01-30

### Added
- Add support for active node state when using non-query string pagination.

### Fixed
- Fix icon alignment for nodes when new window or title override is set.

## 2.0.24 - 2024-01-06

### Fixed
- Fix not being able to manage navigations with `bypassProjectConfig` enabled.
- Fix the “Edit” button on nodes not appearing after saving a node.

## 2.0.23 - 2023-12-08

### Added
- Add `navigation/navs/fix-sites` console command to fix missing `navigation_navs_sites` entries for failed Craft 3 > 4 migrations.

### Changed
- Change max-level check when moving elements to base-plugin for performance.
- Swap `hasDescendants` with `children` to make use of eager-loading performance.

### Fixed
- Fix element node modal’s site not changing to the same site as the navigation.
- Fix validation checks when moving nodes between levels.
- Fix an error when adding elements to a navigation.
- Fix the “Edit” button on nodes not appearing after saving a node.

## 2.0.22 - 2023-10-25

### Added
- Add Markdown support for navigation intstructions.

### Fixed
- Fix being able to circumvent max nodes level settings when moving nodes in the structure.
- Fix custom UI for element index showing when viewing Nodes outside of Navigation.

## 2.0.21 - 2023-09-08

### Added
- Add the ability to use `NodeQuery` objects in all `craft.navigation.*` Twig calls.
- Add missing french translations. (thanks @pascalminator).

### Fixed
- Fix element fields not saving correctly in Craft 4.4+.
- Fix some custom field values not saving correctly.
- Fix an error when restoring a deleted nav.

## 2.0.20 - 2023-08-09

### Added
- Add `aria-current=“page”` to `craft.navigation.render()`.
- Add `Node::getCurrent()`.

### Fixed
- Fix an error when running `resave/navigation-nodes`.

## 2.0.19 - 2023-07-11

### Added
- Add `NodeType::beforeSaveNode`.
- Add `NodeType::getDefaultTitle`.

### Fixed
- Fix Site not not using the site name as the default title.

## 2.0.18 - 2023-05-27

### Fixed
- Fix incorrect sources for element nodes in the element slide-out.
- Fix when editing an existing navigation, being unable to pick site-specific elements (for element nodes).

## 2.0.17 - 2023-03-21

### Changed
- Improve querying nodes performanc for large sites (with a large project config).

### Fixed
- Fix an error when adding new nodes for large navigations.
- Fix search and sort filters showing when editing a navigation.
- Fix a JS error for users with the control panel set to a language containing special characters, and when switching sites when editing nodes.
- Fix being unable to delete a navigation from the edit screen.

## 2.0.16 - 2023-01-06

### Changed
- Only admins are now allowed to access plugin settings.

### Fixed
- Fix a Craft 3 > Craft 4 migration.

## 2.0.15 - 2022-12-14

### Fixed
- Fix an error when switching node types to non-elements.

## 2.0.14 - 2022-12-03

### Fixed
- Fix an issue where `navigation_sites` database entries weren’t being created correctly.

## 2.0.13 - 2022-11-22

### Fixed
- Fix an issue where `navigation_sites` database entries weren’t being created correctly for Craft 3 > Craft 4 upgrades.

## 2.0.12 - 2022-11-21

### Changed
- Element nodes now throw a validation error when a linked element is not selected.

### Fixed
- Fix an issue where empty custom attributes would create invalid HTML.

## 2.0.11 - 2022-11-09

### Added
- Add “Edit Nodes” button when editing a navigation’s settings.

### Fixed
- Fix validation not working correctly when setting “Max Levels”, “Max Nodes” or “Max Nodes per Level”.
- Fixed PHP errors that could occur when executing GraphQL queries.
- Fix GraphQL queries on a Navigation field when no navigation is chosen returning incorrect nodes.

## 2.0.10 - 2022-10-28

### Fixed
- Fix an error when migrating from 2.0.8.

## 2.0.9 - 2022-10-28

### Added
- Add the ability to set "Propagation Method" on navigations, to include site group, or language-specific propagation settings.
- Add “Max Nodes per Level” navigation setting to control the number of nodes per-level.
- Add ability to duplicate navigation.
- Add error-handling for GraphQL queries when the schema didn’t allow querying on linked element types.

### Changed
- Nodes are propagated to all enabled sites for the navigation by default (for multi-sites).
- When changing the "Propagation Method" for navigations, nodes are now re-saved via a queue job, to assist with large navigations.

### Fixed
- Fix some issues with node propagation.
- Fix element actions not appearing due to Craft 4.3 changes.

### Removed
- Removed A&M Nav and Navee Craft 2 migrations, as these are no longer applicable in Craft 4.

## 2.0.8 - 2022-10-25

### Added
- Add support for GraphQL querying on Navigation field within other elements.

## 2.0.7 - 2022-10-17

### Added
- Add the ability to set conditions on navigation field layout attributes and fields depending on node type.
- Add `element` and `elementType` to breadcrumb items.
- Add back `link` attribute for breadcrumbs.

### Changed
- Set dirty attributes for track changes when saving a node.

### Fixed
- Fix an error when saving a node, when switching its type.
- Fix a formatting issue when saving nodes and the toast notification.
- Fix the “Parent” value resetting after adding a node.

## 2.0.6 - 2022-09-25

### Changed
- Revamp `breadcrumbs()` function to include non-elements.

### Fixed
- Fix incorrect node/list names for `gatsby-source-craft` plugin.
- Fix reordering a navigation with `bypassProjectConfig` enabled.
- Fix deleting a navigation with `bypassProjectConfig` enabled.
- Fix a Craft 3 migration error when navigations contain no site settings.

## 2.0.5 - 2022-08-31

### Added
- Add migration to fix non-multi-site’s not being enabled.

### Fixed
- Fix a Craft 3 migration issue where site-specific navigations weren’t marked as enabled.
- Fix an error running `resave` console commands.

## 2.0.4 - 2022-08-25

### Added
- Add site dropdown to navigation index.
- Add missing English Translations.

### Fixed
- Fix `getSiteIds()` not returning correctly for just the enabled sites.
- Fix an error when uninstalling.

## 2.0.3 - 2022-07-02

### Added
- Changes from 1.4.27.

## 2.0.2 - 2022-06-01

### Fixed
- Fix an error when migrating non-multisites from Craft 3.
- Fix incorrect permission check for structure items, when editing nodes.
- Fix an error when trying to fetch parent nodes for a non-multi-site install when editing nodes.

## 2.0.1 - 2022-05-18

### Added
- Added `active`, `target` and `element` items to `buildNavTree`.
- Added French translation (thanks @pascalminator).
- Added `hasChildrenClass` setting to `navigation.render`. Now also includes a `nav-children` class on `<li>` elements that have children.

### Changed
- Using `navgation.render()` now uses eager-loading by default.
- Move `Add a …` strings to `navigation` translation strings.

### Fixed
- Fixed missing instruction text when editing a navigation.
- Fixed `node.hasActiveChild` returning `true` incorrectly.

## 2.0.0 - 2022-05-06

### Added
- When editing nodes, you can now toggle the status of nodes, view trashed nodes, and restore nodes.
- When editing nodes, you can now duplicate, duplicate with descendants, delete, delete with descendants with multiple nodes.
- When editing nodes, you can now add UI elements to node layouts.
- Added the ability to set the default placement of new nodes, when adding them to a navigation.
- Added the ability to set the color for custom nodes and registered elements, which show as the color indicator on the type of node in the navigation builder.
- Add nested node support for Feed Me.
- Add checks for registering events for performance.
- Add `project-config/rebuild` support.
- Add `archiveTableIfExists()` to install migration.

### Changed
- Now requires PHP `8.0.2+`.
- Now requires Craft `4.0.0+`.
- Now requires Navigation `1.4.24` in order to update from Craft 3.
- Redesigned UI for editing navigation nodes.
- When editing nodes, toggling the site is much quicker.
- When editing nodes, we now lazy-load collapsed nodes to improve performance.
- Editing nodes is now performed through slide-out menu, instead of HUD. This gives users much more room to edit content.
- Editing nodes now properly supports multi-tabs for custom fields and UI elements.
- All node attributes are now native fields. They can be included or excluded as needed, with the bonus of supporting conditions (show certain attributes based on user permissions). This can help simplify and streamline editing nodes for  users.
- When editing nodes, URL Suffix, Classes and Custom Attributes are now in an "Advanced" tab.
- Changed `Node::isManual()` to `Node::isCustom()`.
- Custom URL nodes are now a Node Type - `verbb\navigation\nodetypes\CustomType`.
- Rename base plugin methods.
- Support new `DefineElementInnerHtmlEvent` event for modifying element index html.

### Fixed
- Fix an error with GraphQL.
- Fix an error with incorrect NodeType casting.
- Fix an error if Commerce is is enabled, but doesn’t exist.
- Fix some scenarios in the navigation builder, when you were unable to nest nodes under another node, or be able to un-nest.
- Fix an error when trying to set a new nodes parent.
- Fix a nested node having its level reset when saving.
- Fix another legacy site settings check.
- Fix custom node’s URLs being blank.
- Fix an error with Feed Me beta.
- Fix icon alignment for node table rows.
- Fix being unable to save non-element type nodes.
- Fix lack of checking for node element in some places.
- Fix an error when trying to create a navigation on a non-multi-site.

### Removed
- Removed `NodeType::hasClasses()` and `NodeType::hasAttributes()` which are now controlled by native fields.

## 1.4.31 - 2022-11-21

### Fixed
- Fix an issue where empty custom attributes would create invalid HTML.

## 1.4.30 - 2022-10-28

### Fixed
- Fix URL Suffix setting not being reset when changing from an element to non-element node type.

## 1.4.29 - 2022-10-25

### Added
- Add support for GraphQL querying on Navigation field within other elements.

## 1.4.28 - 2022-07-15

### Fixed
- Fix an error when resaving nodes for a site.

## 1.4.27 - 2022-07-02

### Fixed
- Fix partial URLs incorrectly marked as active `/newsletter` and `/news`.
- Fix duplicated nodes when not propagating nodes, when a new site is enabled in the nav settings, and existing nodes already exist.

## 1.4.26 - 2022-04-23

### Fixed
- Fix fetching element sources when rendering nodes (causing of many things, user temporary upload folders to be created) and improve performance.
- Fix SQL query error when trying to restore trashed nodes for a nav.

## 1.4.25 - 2022-04-06

### Added
- Add more error handling to navigation migrations.
- Add `resave/navigation-nodes` CLI command to resave nodes in bulk.

### Changed
- Improve performance of `node->url`.

### Fixed
- Fix an error with Navee migration and parent nodes.
- Fix `linkAttributes()` not merging in attributes defined in the control panel for the node, with template attributes
- Fix an error when migrating navigations from A&M Nav or Navee regarding enabled sites.

## 1.4.24 - 2022-01-22

### Fixed
- Fix when turning on node propagation, node elements aren't re-saved in each site (for multi-sites).
- Fix an error when turning off node propagation, which would effect all navigation nodes, instead of the navigation being edited.

## 1.4.23 - 2021-12-31

### Fixed
- Fix custom node types not having their class set correctly in the control panel.
- Fix validation errors not appearing when saving a navigation.

## 1.4.22 - 2021-10-30

### Changed
- Now requires Craft 3.6.0+.

### Fixed
- Fix Navee migration, where nodes weren't maintaining their structure. (thanks @iainsaxon).
- Fix GraphQL generator issues in some cases (Gatsby Helper).
- Fix an error with Gatsby Helper plugin.
- Fix when deleting navigations their nodes not being marked as deleted, on a multi-site, without node propagation disabled.

## 1.4.21 - 2021-08-25

### Fixed
- Improve `displayName` twig function.
- Fix an error when saving a linked element, when the node was deleted.

## 1.4.20 - 2021-07-19

### Fixed
- Fix HUD overflow issue for some plugins (Icon Picker).
- Fix an error when propagating elements on multi-sites, where navigation nodes didn’t support the site an element is propagating into.

## 1.4.19 - 2021-07-10

### Fixed
- Fix an error when saving settings for the first time. (thanks @boboldehampsink).

## 1.4.18 - 2021-06-20

### Fixed
- Fix when turning off node propagation, duplicate nodes would occur.
- Fix when turning off node propagation, the node hierarchy and order would be incorrect.
- Fix nodes not propagating correctly when a navigation was turned on. Nodes should only be duplicated when turned **off**.

## 1.4.17 - 2021-06-05

### Changed
- Improve query performance for large sites (with a large project config). Typically a 50-70% improvement in rendering speed.

### Fixed
- Fix `navHandle` and `navName` for GraphQL producing errors.

## 1.4.16 - 2021-05-08

### Added
- Add edit structure authorize to save node controller action.
- Add validation rules for navigations to ensure at least one site is enabled, for multi-site installs.

### Fixed
- Fix an error when adding a new site, and propagating nodes.
- Fix an error when saving a site node.
- Fix querying navigation nodes with GraphQL with only “View all navigations” schema permissions set.
- Fix `getActiveNode()` no matching a node if the current URL contained a query string.
- Fix potential error when non-element type nodes have element information leftover.

## 1.4.15 - 2021-03-13

### Fixed
- Fix an error when trying to view the default navigation, if the user didn’t have permission to access the primary site.
- Fix an error that would occur when switching an element node to a non-element node (Entry to Passive).
- Fix type label not updating when switching to a different node type.

## 1.4.14 - 2021-03-04

### Fixed
- Fix `nodeUri` returning the incorrect value for multi-sites with GraphQL.
- Fix when no site selected, the primary site not being used when editing a navigation.
- Fix when selecting element nodes on multi-sites, the element selector modal now defaults to the currently editing site.

## 1.4.13 - 2021-01-26

### Fixed
- Fix passive nodes being marked as active.
- Ensure active state checks only check against nodes with URLs.
- Fix potential error with GraphQL and querying `customAttributes` and `data`.

## 1.4.12 - 2021-01-15

### Added
- Add support for [Gatsby Helper](https://github.com/craftcms/gatsby-helper).

### Fixed
- Fix “Clear Nodes” clearing the primary site’s nodes, instead of the currently-editing site’s nodes.
- Fix incorrect redirect when clearing nodes.
- Fix when disabling propagation, node levels weren’t being retained and resetting to the root level.

## 1.4.11 - 2021-01-14

### Fixed
- Fix for non-absolute, non-root-relative nodes not getting their active state set correctly.

## 1.4.10 - 2020-12-22

### Fixed
- Fix `getSupportedSites()` for a node always returning all sites.

## 1.4.9 - 2020-12-15

### Added
- Add `node.isSite()` and `node.isPassive()`.

### Fixed
- Fix a potential error during migration, where a “All elements must have at least one site associated with them” warning might appear.
- Fix an error when trying to edit a navigation with no enabled sites.

## 1.4.8 - 2020-12-11

### Fixed
- Fix `EVENT_REGISTER_GQL_SCHEMA_COMPONENTS` error when running Navigation on Craft 3.4.x sites.
- Fix showing sites where a user might not have permission to access.
- Fix when setting a navigation to not propagate nodes, existing nodes should be created for new sites.

## 1.4.7 - 2020-12-07

### Fixed
- Fix node type not persisting to a custom URL when editing a node (again).

## 1.4.6 - 2020-12-07

### Changed
- Navigation node queries via GraphQL are now no longer automatically included in the public schema.

### Fixed
- Fix node type not persisting to a custom URL when editing a node.
- Fix nodes propagating to all user-enabled sites, instead of the site a navigation is enabled for.

## 1.4.5 - 2020-12-02

### Fixed
- Fix potential issue with Feed Me throwing errors when not installed or found.
- Fix node type dropdown in node edit modal not showing the correct enabled node types.

## 1.4.4 - 2020-11-29

### Changed
- Node field layout designer no longer allows tabs of fields.

### Fixed
- Fix error when accessing navigation nodes for a navigation that was not enabled.
- Fix list of editable navigations not being correct, when editing a navigation. This could cause loading the incorrect (un-editable) nav in some instances.
- Fix node custom fields not showing overrides (label, instructions) as defined in the field layout designer.

## 1.4.3 - 2020-11-16

### Added
- Add `hasUrl()` node query param.

### Fixed
- Fix element permissions for navigations not working correctly for non-english users.
- Allow navigation instructions to include line-breaks.

## 1.4.2 - 2020-11-03

### Added
- Add support for all registered elements, including third-party ones that support `hasUris`. Element support can be managed in the navigation settings.
- Add URL for element nodes when hovering over the node type.
- Add URL for element nodes in node edit modal.

### Changed
- Refactor render template to use Twig `attr` function for cleaner templates.

### Fixed
- Fix unnecessary empty attributes being outputted when using `craft.navigation.render()`.

## 1.4.1 - 2020-10-20

### Fixed
- Fix unnecessary additional queries for nodes.

## 1.4.0 - 2020-10-18

### Added
- Significantly improved performance when adding multiple nodes at once.
- Add passive node type. Perfect for headings, dividers or other UI-related nodes that don't have a URL.
- Add “Clear Nodes” button when editing navigations.
- Add “Settings” button when editing navigations.
- Add Permissions to navigations, allowing specific sections/groups/volumes/etc to be enabled to add elements from.
- Add Enable/Disable for each node type for navigations. Allows easier customising of available nodes to add from.
- Add Feed Me support. Navigation nodes can now be imported using Feed Me.
- Add site settings to navigations, to control which sites can have the navigation enabled for.
- Add `hasAttributes` function to node types.

### Changed
- Removed `disabledElements` config setting. This is now managed at the plugin level.

### Fixed
- Fix active node checking when special characters are in URLs.
- Navee migration is now complete, handling migrating nested nodes.
- Fix modal node editor not respecting node type settings for field options available to edit.
- Fix adding multiple nodes at once often being added out of order.

## 1.3.31 - 2020-09-29

### Added
- Add `propagateSiteElements` config setting to help with multi-site menus. Navigation will use the equivalent element for each site automatically for multi-site menus. But you might want to pick specific elements across your sites, and have them the same across all your navigations.

## 1.3.30 - 2020-09-27

### Fixed
- Fix homepage entry active state check when outputting multiple different sites navs on a single site.
- Fix error when trying to get the active state for a node with an empty URL.
- Fix custom attributes in GraphQL not returning an object.

## 1.3.29 - 2020-09-16

### Fixed
- Fix detection of active parameter on multi-site setups.
- Fix nodes being unlinked to their element in some special circumstances.

## 1.3.28 - 2020-08-31

### Changed
- For site nodes, the trailing slash is now trimmed for the site URL.

### Fixed
- Fix node being marked as active for partial matches where the URLs contain the same words.
- Fix default site when editing a nav to be the first editable for a user.

## 1.3.27 - 2020-08-20

### Fixed
- Fix edit menu button showing with `allowAdminChanges = false`.
- Fix overflow in model node edit window, effecting some custom fields.

## 1.3.26 - 2020-08-14

### Fixed
- Fix active-state check for nodes, which weren't catering for multi-sites with a sub-directory in their base url.

## 1.3.25 - 2020-08-11

### Fixed
- Update `node->uri` to `node->nodeUri`, prevents URI issues when saving elements.
- Update migration to log potential errors.

## 1.3.24 - 2020-08-10

### Added
- Add settings button when editing a navigation.
- Add `uri` property to node.

### Fixed
- Fix incorrect translation for settings in navigation index.
- Fix `elementSiteId` error caused by Craft 3.5 changes.

## 1.3.23 - 2020-08-05

### Fixed
- Fix `elementSiteId` not saving for nodes in Craft 3.5+.
- Fix deprecation notice for `enabledForSite`.

## 1.3.22 - 2020-07-22

### Fixed
- Fix custom node types with `hasTitle = false`.
- Fix custom node types with long names in the CP.
- Ensure the the primary site is selected when editing a nav, rather than the first editable nav.

## 1.3.21 - 2020-06-24

### Fixed
- Fix element still being referenced for a node when switching it to a custom URL..

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
