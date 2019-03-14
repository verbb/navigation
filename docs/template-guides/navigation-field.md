# Navigation Field

You can use the navigation field to allow entries and other elements to select a navigation to show. In your templates, when calling the field (ie `{{ entry.navigationField }}`) you'll be returned the handle for the navigation.

```twig
{{ craft.navigation.render(entry.navigationField) }}
```