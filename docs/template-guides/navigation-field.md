# Navigation Field

You can use the navigation field to allow entries and other elements to select a navigation to show. In your templates, when calling the field (ie `{{ entry.navigationField }}`) you'll be returned a Nav Model. From this, you have access to the navigation's name, handle and any other settings.

```twig
Here's the {{ entry.navigationField.name }} navigation:

{{ craft.navigation.render(entry.navigation.handle) }}
```

If calling `{{ entry.navigation }}`, you'll be returned the handle of the navigation.