# Node Types

Choose a node type according to what the menu item should do. A link to a Craft entry can follow that entry’s title and URL; a manually entered URL can point outside the site; a label can group links without taking visitors to another page.

Open a menu’s builder and choose a type from the sidebar to add an item. Edit an existing item in its slide-out to change its settings, then save the menu. The menu’s **Permissions** tab controls which types and sources editors can choose.

## Link to Craft Content

Use **Entries**, **Categories**, or **Assets** when a menu item should point to content managed in Craft. Choose the relevant element in the picker. For example, selecting the About entry lets its menu link follow that entry’s URL when its slug changes. You can override the node title when the menu needs a shorter label.

**Products** is available when Commerce is installed and enabled. The same approach lets you link to a product without entering its URL manually.

## Enter a URL

Use **Custom** for a destination such as `/contact` or an external website. Enter a title and URL in the builder. Because the URL is entered manually, update it yourself if the destination changes.

On multisite projects, **Site** links to a Craft site’s base URL. It can identify that site’s pages as active, which is useful for a header linking between brands or locales.

## Group Links

A **Passive** item is a label without a destination. For example, Shop can group links to Products, Sale, and New Arrivals. The default rendering uses a `span`; it does not create an interactive dropdown by itself. Follow [Build a Header Menu with Dropdowns](/user-guides/templating/build-a-header-menu-with-dropdowns) to turn the group into an operable disclosure.

Use **GroupColumn** when custom templates need to arrange links into columns. The type supplies a structural item with no URL; [Build a Mega Menu](/user-guides/templating/build-a-mega-menu) shows how to render the columns.

## Keep a List Up to Date with Dynamic Nodes

Use **Dynamic** when a menu should list matching content automatically, such as the latest five News entries. Choose **Entries** as the source, select the News section, and configure the available ordering and limit controls in the slide-out. Save the menu and view it on your site.

Matching entries appear as children when the menu is read. You do not need to add another stored node for every new entry. Any manually added children appear before these generated children. These generated items are called projected nodes; they provide links and linked-element data but do not have their own node custom fields.

Built-in sources cover Entries, Categories, Assets, and Commerce Products. The controls depend on the source you choose. For a complete example, see [Auto-List a Section with Dynamic Nodes](/user-guides/templating/auto-list-a-section-with-dynamic-nodes).

## Extend the Available Types

A module or plugin can register types with custom behaviour and additional Dynamic sources. Developers can start with [Node Types](/developers/node-types) and [Dynamic Source Events](/developers/events#the-registerdynamicsources-event). The [Node reference](/reference/node) describes the objects used in templates.
