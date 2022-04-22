# GraphQL
Navigation supports accessing [Node](docs:developers/node) objects via GraphQL. Be sure to read about [Craft's GraphQL support](https://docs.craftcms.com/v3/graphql.html).

## Nodes

### Example

:::code
```graphql GraphQL
{
    nodes (navHandle: "mainMenu", level: 1) {
        title
        url
        children {
            title
            url
        }
    }
}
```

```json JSON Response
{
    "data": {
        "nodes": [
            {
                "title": "About",
                "url": "http://craft.test/about",
                "children": [
                    {
                        "title": "Who We Are",
                        "url": "http://craft.test/about/who-we-are"
                    }
                ]
            }
        ]
    }
}
```
:::


### The `nodes` query
This query is used to query for [Node](docs:developers/node) objects. You can also use the singular `node` to fetch a single node.

| Argument | Type | Description
| - | - | -
| `id`| `[QueryArgument]` | Narrows the query results based on the elements’ IDs.
| `uid`| `[String]` | Narrows the query results based on the elements’ UIDs.
| `status`| `[String]` | Narrows the query results based on the elements’ statuses.
| `archived`| `Boolean` | Narrows the query results to only elements that have been archived.
| `trashed`| `Boolean` | Narrows the query results to only elements that have been soft-deleted.
| `site`| `[String]` | Determines which site(s) the elements should be queried in. Defaults to the current (requested) site.
| `siteId`| `[QueryArgument]` | Determines which site(s) the elements should be queried in. Defaults to the current (requested) site.
| `unique`| `Boolean` | Determines whether only elements with unique IDs should be returned by the query.
| `preferSites`| `[QueryArgument]` | Determines which site should be selected when querying multi-site elements.
| `enabledForSite`| `Boolean` | Narrows the query results based on whether the elements are enabled in the site they’re being queried in, per the `site` argument.
| `title`| `[String]` | Narrows the query results based on the elements’ titles.
| `slug`| `[String]` | Narrows the query results based on the elements’ slugs.
| `uri`| `[String]` | Narrows the query results based on the elements’ URIs.
| `search`| `String` | Narrows the query results to only elements that match a search query.
| `relatedTo`| `[QueryArgument]` | Narrows the query results to elements that relate to the provided element IDs. This argument is ignored, if `relatedToAll` is also used.
| `relatedToAssets`| `[AssetCriteriaInput]` | Narrows the query results to elements that relate to an asset list defined with this argument.
| `relatedToEntries`| `[EntryCriteriaInput]` | Narrows the query results to elements that relate to an entry list defined with this argument.
| `relatedToUsers`| `[UserCriteriaInput]` | Narrows the query results to elements that relate to a use list defined with this argument.
| `relatedToCategories`| `[CategoryCriteriaInput]` | Narrows the query results to elements that relate to a category list defined with this argument.
| `relatedToTags`| `[TagCriteriaInput]` | Narrows the query results to elements that relate to a tag list defined with this argument.
| `relatedToAll`| `[QueryArgument]` | Narrows the query results to elements that relate to *all* of the provided element IDs. Using this argument will cause `relatedTo` argument to be ignored. **This argument is deprecated.** `relatedTo: ["and", ...ids]` should be used instead.
| `ref`| `[String]` | Narrows the query results based on a reference string.
| `fixedOrder`| `Boolean` | Causes the query results to be returned in the order specified by the `id` argument.
| `inReverse`| `Boolean` | Causes the query results to be returned in reverse order.
| `dateCreated`| `[String]` | Narrows the query results based on the elements’ creation dates.
| `dateUpdated`| `[String]` | Narrows the query results based on the elements’ last-updated dates.
| `offset`| `Int` | Sets the offset for paginated results.
| `limit`| `Int` | Sets the limit for paginated results.
| `orderBy`| `String` | Sets the field the returned elements should be ordered by.
| `siteSettingsId`| `[QueryArgument]` | Narrows the query results based on the unique identifier for an element-site relation.
| `withStructure`| `Boolean` | Explicitly determines whether the query should join in the structure data.
| `structureId`| `Int` | Determines which structure data should be joined into the query.
| `level`| `Int` | Narrows the query results based on the elements’ level within the structure.
| `hasDescendants`| `Boolean` | Narrows the query results based on whether the elements have any descendants in their structure.
| `ancestorOf`| `Int` | Narrows the query results to only elements that are ancestors of another element in its structure, provided by its ID.
| `ancestorDist`| `Int` | Narrows the query results to only elements that are up to a certain distance away from the element in its structure specified by `ancestorOf`.
| `descendantOf`| `Int` | Narrows the query results to only elements that are descendants of another element in its structure provided by its ID.
| `descendantDist`| `Int` | Narrows the query results to only elements that are up to a certain distance away from the element in its structure specified by `descendantOf`.
| `leaves`| `Boolean` | Narrows the query results based on whether the elements are “leaves” in their structure (element with no descendants).
| `nextSiblingOf`| `Int` | Narrows the query results to only the entry that comes immediately after another element in its structure, provided by its ID.
| `prevSiblingOf`| `Int` | Narrows the query results to only the entry that comes immediately before another element in its structure, provided by its ID.
| `positionedAfter`| `Int` | Narrows the query results to only entries that are positioned after another element in its structure, provided by its ID.
| `positionedBefore`| `Int` | Narrows the query results to only entries that are positioned before another element in its structure, provided by its ID.
| `nav'`| `[String]` | Narrows the query results based on the navigation the node belongs to.
| `navHandle`| `String` | Narrows the query results based on the provided navigation handle.
| `navId`| `Int` | Narrows the query results based on the provided navigation ID.
| `type`| `[String]` | Narrows the query results based on the node’s type.


### The `NodeInterface` interface
This is the interface implemented by all nodes.

| Field | Type | Description
| - | - | -
| `id`| `ID` | The ID of the entity.
| `uid`| `String` | The UID of the entity.
| `_count`| `Int` | Return a number of related elements for a field.
| `title`| `String` | The element’s title.
| `slug`| `String` | The element’s slug.
| `uri`| `String` | The element’s URI.
| `enabled`| `Boolean` | Whether the element is enabled or not.
| `archived`| `Boolean` | Whether the element is archived or not.
| `siteId`| `Int` | The ID of the site the element is associated with.
| `siteSettingsId`| `ID` | The unique identifier for an element-site relation.
| `language`| `String` | The language of the site element is associated with.
| `searchScore`| `String` | The element’s search score, if the `search` parameter was used when querying for the element.
| `trashed`| `Boolean` | Whether the element has been soft-deleted or not.
| `status`| `String` | The element’s status.
| `dateCreated`| `DateTime` | The date the element was created.
| `dateUpdated`| `DateTime` | The date the element was last updated.
| `lft`| `Int` | The element’s left position within its structure.
| `rgt`| `Int` | The element’s right position within its structure.
| `level`| `Int` | The element’s level within its structure
| `root`| `Int` | The element’s structure’s root ID.
| `structureId`| `Int` | The element’s structure ID.
| `children`| `[NodeInterface]` | The node’s children.
| `parent`| `NodeInterface` | The node’s parent.
| `prev`| `NodeInterface` | Returns the previous element relative to this one, from a given set of criteria.
| `next`| `NodeInterface` | Returns the next element relative to this one, from a given set of criteria.
| `elementId`| `Int` | The ID of the element this node is linked to.
| `navId`| `Int` | The ID of the navigation this node belongs to.
| `navHandle`| `String` | The handle of the navigation this node belongs to.
| `navName`| `String` | The name of the navigation this node belongs to.
| `type`| `String` | The type of node this is.
| `classes`| `String` | Any additional classes for the node.
| `customAttributes`| `CustomAttribute` | Any additional custom attributes for the node.
| `data`| `String` | Any additional data for the node.
| `newWindow`| `String` | Whether this node should open in a new window.
| `url`| `String` | The node’s full URL',
| `urlSuffix`| `String` | The URL for this navigation item.
| `nodeUri`| `String` | The node’s URI',
| `element`| `ElementInterface` | The element the node links to.

### The `CustomAttribute` interface
This is the interface used for custom attributes.

| Field | Type | Description
| - | - | -
| `attribute`| `String` | The attribute.
| `value`| `String` | The value.

