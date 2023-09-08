# Available Variables

The following methods are available to call in your Twig templates:

### `craft.navigation.nodes(params)`
The `params` parameter can be either a string for the [Nav](docs:developers/nav) handle, or an object of [NodeQuery](docs:getting-elements/node-queries) params. You can also chain these same params to this function call.

```twig
{# Fetch the `mainMenu` nodes #}
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

{# Chain params to the `nodes()` function #}
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .site('default')
    .all() %}

{# Or, pass them as an object #}
{% set nodes = craft.navigation.nodes({
    handle: 'mainMenu',
    site: 'default',
}).all() %}
```

See [Node Queries](docs:getting-elements/node-queries)

### `craft.navigation.render(params, options)`
The `params` parameter can be either a string for the [Nav](docs:developers/nav) handle, an object of [NodeQuery](docs:getting-elements/node-queries) params or a [NodeQuery](docs:getting-elements/node-queries) itself.

```twig
{# Render the `mainMenu` navigation #}
{{ craft.navigation.render('mainMenu') }}

{# Render the `mainMenu` navigation for the `default` site #}
{{ craft.navigation.render({
    handle: 'mainMenu',
    site: 'default',
}) }}

{# The same as above, but using a `NodeQuery` #}
{% set nodeQuery = craft.navigation.nodes('mainMenu').site('default') %}

{{ craft.navigation.render(nodeQuery) }}
```

See [Rendering Nodes](docs:template-guides/rendering-nodes)

### `craft.navigation.breadcrumbs(options)`
See [Breadcrumbs](docs:template-guides/breadcrumbs)

### `craft.navigation.getActiveNode(params, includeChildren)`
The `params` parameter can be either a string for the [Nav](docs:developers/nav) handle, an object of [NodeQuery](docs:getting-elements/node-queries) params or a [NodeQuery](docs:getting-elements/node-queries) itself.

See [Rendering Nodes](docs:template-guides/rendering-nodes)

### `craft.navigation.tree(params)`
Returns a full tree structure of nodes as a nested array.

The `params` parameter can be either a string for the [Nav](docs:developers/nav) handle, an object of [NodeQuery](docs:getting-elements/node-queries) params or a [NodeQuery](docs:getting-elements/node-queries) itself.

### `craft.navigation.getNavById(id)`
Returns the navigation for the provided id.

### `craft.navigation.getNavByHandle(handle)`
Returns the navigation for the provided handle.
