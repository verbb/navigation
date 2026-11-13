# Queries

Query a menu’s links with GraphQL when your frontend needs to choose the fields it receives. The examples on this page use a saved menu called **Main Menu**, with the handle `mainMenu`, enabled for the site you want to query.

## Configure Schema Access

Navigation’s queries are only available to a schema that can read at least one menu. In Craft’s **GraphQL → Schemas**, edit the schema your client will use. Under **Navigation**, enable **View menu - Main Menu**, then save. Each menu has its own **View menu** option, so a schema can read some menus and not others. Use **View all menus** only when that schema should read every menu, including menus created later.

| Schema option | Lets the schema read |
| --- | --- |
| **View all menus** | Every menu, along with its nodes and menu-level fields. |
| **View menu - [Menu]** | That one menu, along with its nodes and menu-level fields. |

Follow [Craft’s GraphQL setup](https://craftcms.com/docs/5.x/development/graphql.html) to create an endpoint and choose a public schema or a token for a private schema. Test with that same schema in GraphiQL, because the administrator’s full schema can hide a missing permission. Keep private tokens in server-side code.

Menu access is separate from access to linked content. If you request a node’s linked `element`, the schema must also allow that element’s section, category group, volume or product type, and the linked element’s site. When it doesn’t, `element` returns `null` while the node itself is still returned.

Run the examples below in GraphiQL with your intended schema. If `navigationNodes` isn’t available, check the schema’s Navigation options. If the results are empty, check the menu handle, the site, and that the menu and its nodes are enabled.

## Node Queries

### The `navigationNodes` Query

Returns a list of nodes, each as a [NodeInterface](/graphql/interfaces-types#the-nodeinterface-interface). Use it to fetch a whole menu, one level of a menu, or the nodes of a particular type. Results only include nodes from menus the schema can read.

This query accepts Craft’s [element query arguments](https://craftcms.com/docs/5.x/development/graphql.html#element-query-arguments) and structure arguments such as `level`, `ancestorOf` and `descendantOf`, along with arguments for any custom fields in your menus’ node field layouts. Navigation adds the following arguments, which match the [node query parameters](/getting-elements/node-queries) of the same name.

| Argument | Type | Description |
| --- | --- | --- |
| `menuHandle` | `String` | Narrows the results to nodes in the menu with this handle. |
| `menuId` | `Int` | Narrows the results to nodes in the menu with this ID. |
| `type` | `[String]` | Narrows the results to nodes of these node type classes, such as `verbb\navigation\nodetypes\Custom`. |
| `withLinkedElements` | `Boolean` | Loads the linked entries, categories and other elements for all returned nodes in one batch. This bypasses Navigation’s tree cache. |
| `withNodeHierarchy` | `Boolean` | Connects each node’s parent and children in memory after the query runs, so `children` and `parent` don’t need further queries. Leave it out to let Navigation decide, which it does for menu-scoped front-end queries. |
| `withMenu` | `Boolean` | Loads each node’s menu, including menu-level custom fields, in one batch. This bypasses Navigation’s tree cache. |
| `withProjectedChildren` | `Boolean` | Whether `children` includes the nodes a Dynamic node generates when the menu is read. Pass `false` to leave them out. |

This query returns the root links of Main Menu and their direct children.

```graphql
{
  navigationNodes(menuHandle: "mainMenu", level: 1) {
    id
    title
    url
    children {
      title
      url
    }
  }
}
```

```json
{
  "data": {
    "navigationNodes": [
      {
        "id": "101",
        "title": "Home",
        "url": "https://example.com/",
        "children": []
      },
      {
        "id": "102",
        "title": "About",
        "url": "https://example.com/about",
        "children": [
          {
            "title": "Our Team",
            "url": "https://example.com/about/team"
          },
          {
            "title": "Careers",
            "url": "https://example.com/about/careers"
          }
        ]
      }
    ]
  }
}
```

A secondary site has its own node titles, URLs and enabled states. Pass its handle with `site` to get that site’s version of each node.

```graphql
{
  navigationNodes(menuHandle: "mainMenu", site: "french", level: 1) {
    title
    url
    siteHandle
    language
  }
}
```

```json
{
  "data": {
    "navigationNodes": [
      {
        "title": "Accueil",
        "url": "https://example.com/fr/",
        "siteHandle": "french",
        "language": "fr"
      }
    ]
  }
}
```

The `site` and `siteId` arguments choose which site’s version of each node is returned. For a node linked to an element, the node’s settings for that site decide which site’s version of the element supplies the URL, which can differ from the node’s own site. In a multi-site project, pass `site` or `siteId` whenever you aren’t querying the primary site, and use the same site as your entry queries so URLs and enabled states match your front end. The `language` field on each node is that site’s language, not a separate filter.

To render links with their own attributes, request the node’s settings along with its linked element. This query asks for entry fields through an inline fragment, which only returns data when the schema can read that entry’s section.

```graphql
{
  navigationNodes(menuHandle: "mainMenu", withLinkedElements: true) {
    title
    url
    level
    newWindow
    classes
    customAttributes {
      attribute
      value
    }
    element {
      id
      ... on page_Entry {
        summary
      }
    }
  }
}
```

```json
{
  "data": {
    "navigationNodes": [
      {
        "title": "About",
        "url": "https://example.com/about",
        "level": 1,
        "newWindow": "",
        "classes": "nav-about",
        "customAttributes": [
          {
            "attribute": "data-track",
            "value": "about"
          }
        ],
        "element": {
          "id": "24",
          "summary": "Who we are and what we do."
        }
      }
    ]
  }
}
```

Nodes from a Dynamic node are generated when the menu is read and return as the `ProjectedNavigationNode` type, with `isProjected` set to `true`. Use `__typename` or `isProjected` to tell them apart from saved nodes.

```graphql
{
  navigationNodes(menuHandle: "mainMenu", level: 1) {
    title
    children {
      __typename
      title
      url
      isProjected
    }
  }
}
```

```json
{
  "data": {
    "navigationNodes": [
      {
        "title": "News",
        "children": [
          {
            "__typename": "ProjectedNavigationNode",
            "title": "Autumn Opening Hours",
            "url": "https://example.com/news/autumn-opening-hours",
            "isProjected": true
          }
        ]
      }
    ]
  }
}
```

[Projected Node](/reference/projected-node) explains how these nodes are built. [Interfaces & Types](/graphql/interfaces-types#the-projectednavigationnode-type) lists which of their fields return values.

### The `navigationNode` Query

Returns a single node as a [NodeInterface](/graphql/interfaces-types#the-nodeinterface-interface), or `null` when nothing matches or the schema can’t read the node’s menu. It accepts the same arguments as [`navigationNodes`](#the-navigationnodes-query), and is most often used to fetch one node by `id` or `uid`.

```graphql
{
  navigationNode(id: 102) {
    title
    url
    menuHandle
    parent {
      title
    }
    children {
      title
      url
    }
  }
}
```

```json
{
  "data": {
    "navigationNode": {
      "title": "About",
      "url": "https://example.com/about",
      "menuHandle": "mainMenu",
      "parent": null,
      "children": [
        {
          "title": "Our Team",
          "url": "https://example.com/about/team"
        }
      ]
    }
  }
}
```

## Menu Queries

### The `[handle]_Menu` Query

Returns a menu and its menu-level custom fields. Each menu the schema can read has its own query, named after its handle, such as `mainMenu_Menu` for Main Menu. The query returns the menu’s `[handle]_Menu` type, described in [Interfaces & Types](/graphql/interfaces-types#the-handle-menu-type).

| Argument | Type | Description |
| --- | --- | --- |
| `site` | `String` | The handle of the site to return the menu for. Defaults to the current site. The query returns `null` when the schema can’t read that site. |

In this example, Main Menu’s field layout has a plain text field called `menuHeading`.

```graphql
{
  mainMenu_Menu(site: "default") {
    title
    handle
    menuHeading
  }
}
```

```json
{
  "data": {
    "mainMenu_Menu": {
      "title": "Main Menu",
      "handle": "mainMenu",
      "menuHeading": "Explore"
    }
  }
}
```

To fetch a menu’s fields and its links in one request, combine this query with `navigationNodes`.

```graphql
{
  mainMenu_Menu {
    menuHeading
  }
  navigationNodes(menuHandle: "mainMenu", level: 1) {
    title
    url
  }
}
```

## Context Queries

### The `navigationContext` Query

Returns the [NavigationContext](/graphql/interfaces-types#the-navigationcontext-type) for a menu, which describes where the URL Craft is handling sits in that menu. It mirrors `craft.navigation.context()` in Twig.

| Argument | Type | Description |
| --- | --- | --- |
| `menuHandle` | `String` | The handle of the menu. Required. |

```graphql
{
  navigationContext(menuHandle: "mainMenu") {
    current {
      title
      url
    }
    siblings {
      title
      url
    }
  }
}
```

```json
{
  "data": {
    "navigationContext": {
      "current": {
        "title": "Our Team",
        "url": "https://example.com/about/team"
      },
      "siblings": [
        {
          "title": "Careers",
          "url": "https://example.com/about/careers"
        }
      ]
    }
  }
}
```

The query returns an error when `menuHandle` is missing, or when the menu doesn’t exist or the schema can’t read it. A request to a GraphQL endpoint usually has a different URL from the page your visitor is viewing, so read [Context & Breadcrumbs](/graphql/context-breadcrumbs) before relying on this query in a separate frontend.

### The `navigationMenuBreadcrumbs` Query

Returns a list of [NavigationMenuBreadcrumb](/graphql/interfaces-types#the-navigationmenubreadcrumb-type) items, from the menu’s root down to the deepest node matching the URL Craft is handling. It mirrors `craft.navigation.menuBreadcrumbs()` in Twig, and returns an empty list when no node matches.

| Argument | Type | Description |
| --- | --- | --- |
| `menuHandle` | `String` | The handle of the menu. Required. |

```graphql
{
  navigationMenuBreadcrumbs(menuHandle: "mainMenu") {
    title
    url
    current
  }
}
```

```json
{
  "data": {
    "navigationMenuBreadcrumbs": [
      {
        "title": "About",
        "url": "https://example.com/about",
        "current": false
      },
      {
        "title": "Our Team",
        "url": "https://example.com/about/team",
        "current": true
      }
    ]
  }
}
```

Like `navigationContext`, it returns an error when `menuHandle` is missing or the menu can’t be read. [Context & Breadcrumbs](/graphql/context-breadcrumbs) covers both queries in more detail.

## Querying a Navigation Field

A [Navigation field](/feature-tour/menu-fields) stores a menu handle on an entry or other element. Through GraphQL, the field returns that menu’s nodes as a list of [NodeInterface](/graphql/interfaces-types#the-nodeinterface-interface) items, so you query it inside Craft’s own queries. The field accepts the same arguments as [`navigationNodes`](#the-navigationnodes-query), and returns an empty list when no menu is selected or the schema can’t read the selected menu.

In this example, a `page` entry type has a Navigation field called `sidebarMenu`.

```graphql
{
  entry(section: "pages", slug: "about") {
    title
    ... on page_Entry {
      sidebarMenu(level: 1) {
        title
        url
        children {
          title
          url
        }
      }
    }
  }
}
```

```json
{
  "data": {
    "entry": {
      "title": "About",
      "sidebarMenu": [
        {
          "title": "Our Team",
          "url": "https://example.com/about/team",
          "children": []
        }
      ]
    }
  }
}
```

## Current-Page Information

`NodeInterface` has no `current`, `active` or `hasActiveChild` fields, and a GraphQL request doesn’t inherit the URL open in your frontend. To highlight the current page in the browser, follow [Expose a Menu as JSON](/user-guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend#highlight-the-current-page). To work out the current branch or a breadcrumb trail on the server, use the [context queries](#context-queries).
