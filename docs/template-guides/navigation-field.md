# Navigation Field

Use a Navigation field when editors should choose which menu appears on an entry or another element. For example, a landing page can select its own sidebar menu.

Create a field of type **Navigation** in **Settings → Fields** with handle `pageMenu`, and add it to your entry type’s field layout. Edit an entry, select a saved menu in that field, and save the entry.

In that entry’s Twig template, the field returns the selected menu’s handle. Check for an empty selection before rendering:

```twig
{% if entry.pageMenu %}
    {{ craft.navigation.render(entry.pageMenu) }}
{% endif %}
```

Load the entry’s page and confirm its selected menu appears. Clear the field and save to confirm the menu is omitted. You can pass the selected handle to `nodes()` or `tree()` too; see [Rendering Nodes](/template-guides/rendering-nodes).
