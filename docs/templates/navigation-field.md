# Navigation Field

The Navigation field lets entries and other elements select a menu by handle.

In templates, the field returns the menu **handle** string:

```twig
{{ craft.navigation.render(entry.myNavigationField) }}
```

Use the handle with any `craft.navigation.*` API that accepts a menu handle or criteria array.
