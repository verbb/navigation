# Permissions

Navigation's permissions let you decide who can build each menu. A content team might look after the main menu and footer, a campaign editor might only touch a landing-page menu, and a site builder might create new menus and change their settings. Set them in **Settings → Users → User Groups**, or on an individual user's **Permissions** tab.

**Access Navigation** is listed under **General**, with Craft's other permissions for reaching each plugin's section. The rest are listed under the **Navigation** heading.

| Permission | What It Allows |
| --- | --- |
| Access Navigation | Open the **Navigation** section of the control panel. |
| Create menus | Use **New menu** on the Menus screen to create a menu, and duplicate a menu they can manage. |
| Manage “{menu}” | See the menu on the Menus screen, open its builder, and add, edit, move and delete its nodes. |
| Edit menu settings | Open the menu's settings screen and change its name, handle, sites, limits, field layouts and **Permissions** tab. |
| Delete menu | Delete the menu from the Menus screen. |

Each menu gets its own **Manage** permission, labelled with the menu's name, such as **Manage “Main Menu”**, with **Edit menu settings** and **Delete menu** nested under it. These are built from your saved menus, so a new menu's permissions appear as soon as it's saved and disappear when it's deleted. They're tied to the menu rather than its name or handle, so renaming a menu keeps the access you've already given.

Anyone given one of the other permissions needs **Access Navigation** too, and the Menus screen only lists the menus someone can manage. On a multi-site install, managing a menu on a site also needs Craft's permission to edit that site. Creating a menu, duplicating one and changing a menu's settings all write to Craft's project config, so they're only available where admin changes are allowed, unless `bypassProjectConfig` is turned on. See [Configuration](/get-started/configuration) for that setting.

A menu's **Permissions** tab is separate from these user permissions. It controls which node types can be added to that menu, and which sections, category groups or volumes their elements can come from, for everyone who builds the menu. An editor also needs to be able to view an entry, category or asset to link to it. The [MCP tools](/integrations/mcp) follow the same menu and site permissions as the control panel.

Admins have every Navigation permission. Navigation's **Settings** screen, including importing and exporting menus, is only available to admins. **Edit menu settings** is the one to think about before giving it out, because it controls a menu's sites and field layouts and which node types editors can add, which affects everyone who builds that menu. Most editors only need **Manage** for the menus they look after.
