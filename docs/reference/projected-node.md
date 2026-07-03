# Projected Node

A ProjectedNode object represents a **read-time projection** of a Craft element for **Dynamic** nodes. Projected nodes appear in `node.children`, `{% children %}`, `tree()` output, and GraphQL — but they are **not** stored `Node` elements.

## When you see them

- A menu contains a Dynamic node with a source (for example, the built-in **entry section** source).
- At read time, matching elements are appended as projected children (after any manually stored children).
- Active-state helpers can return a `ProjectedNode` when the current URL matches a projected element.

## Properties

| Property | Description |
| --- | --- |
| `title`, `url` | From the source element |
| `elementId`, `element` | Source Craft element |
| `level`, `siteId` | Structure context |
| `isProjected` | Always `true` |
| `parent` | Parent Dynamic node |
| `active`, `current` | Active flags (via `getActive()` / `getCurrent()` where supported) |

Projected nodes do **not** have node custom fields or CP edit URLs.

## Template example

```twig
{% for child in dynamicNode.children %}
    {% if child.isProjected ?? false %}
        <a href="{{ child.url }}" class="{{ child.getCurrent() ? 'is-current' }}">
            {{ child.title }}
        </a>
    {% endif %}
{% endfor %}
```

## Opt out

Skip projection on a query with **`withProjectedChildren(false)`**. See [Node Queries](/getting-elements/node-queries).

## GraphQL

Projected items use the `ProjectedNavigationNode` type with an `isProjected` field.
