# Creating Your First Menu

A menu collects the links that appear together on your site. Each item in that menu is a node. You'll create a short menu in the control panel and render it in a Twig template.

Open **Navigation → Menus**, choose **New menu**, and use Main Menu as the name and `mainMenu` as the handle. Enable it for the site you are testing and save. Open the menu builder and add two existing entries with public URLs. Drag them into the order you want, then choose **Save menu**.

Place this in the site's layout template where its navigation belongs:

```twig
<nav aria-label="Main">
    {{ craft.navigation.render('mainMenu') }}
</nav>
```

Open the page and check that both links appear in the saved order and reach their entries. Nest one node beneath the other in the builder, save and refresh to check the hierarchy. Your site's CSS controls the appearance of the list.

[Rendering Nodes](docs:templates/rendering-nodes) explains how to customise classes or build your own markup. Add those variations after this first menu works.
