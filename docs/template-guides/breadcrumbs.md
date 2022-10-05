# Breadcrumbs

## `craft.navigation.breadcrumbs(options)`
You can retrieve a list of breadcrumbs based on the current URL. They are not based on your navigation items, and instead use the current URL segments. The function will look up any element that matches the URI for the segment. If not found, the segment itself will be used.

```twig
{% for crumb in craft.navigation.breadcrumbs() %}
    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
{% endfor %}
```

The `crumb` variable returned from the `breadcrumbs()` function will be an array with the following options. This will either contain information on a matched element, or information derived from the segment.

### Properties

| Property | Description
| - | -
| `title` | The title of the segment. Either the element's title, or derived from the segment.
| `url` | The absolute URL for the segment, for the current site.
| `segment` | The segment portion of the current URL.
| `isElement` | Whether the segment is an element or not.
| `element` | The element object (if an element).
| `elementId` | The ID of the element (if an element).
| `elementType` | The type of element (if an element).

You can also pass in options to the `breadcrumbs()` function. For example, you could limit the number of breadcrumb items returned.

```twig
{% for crumb in craft.navigation.breadcrumbs({ limit: 10 }) %}
    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
{% endfor %}
```

### Available Options

| Options | Description
| - | -
| `limit` | The number to limit returned breadcrumbs item by.
