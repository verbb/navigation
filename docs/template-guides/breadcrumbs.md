# Breadcrumbs

### craft.navigation.breadcrumbs()

You can retrieve a list of elements to be used as breadcrumbs. They are not based on your navigation items, and instead use the current URL segments.

```twig
{% for crumb in craft.navigation.breadcrumbs() %}
    {{ crumb.link }}
{% endfor %}
```
