# Expose a Menu as JSON for a JS Frontend

You can serve a menu as JSON and let JavaScript build its links. This guide creates a public endpoint for `mainMenu`, displays the tree in a browser, and highlights links using the page the visitor is actually viewing.

Start with a saved `mainMenu` containing public links and a Craft site that renders Twig templates. You need access to `config/routes.php`, `template-guides/`, and your frontend’s HTML and JavaScript. If Craft runs with `headlessMode` enabled, use [GraphQL](/graphql/query-nodes) instead of this template route.

## Create the JSON Endpoint

Create `template-guides/_api/navigation-main-menu.twig`:

```twig
{% header 'Content-Type: application/json; charset=utf-8' %}
{% header 'Cache-Control: no-store' %}
{% set tree = craft.navigation.tree('mainMenu') %}
{{ tree|json_encode|raw }}
```

The underscore keeps this template out of Craft’s direct template routing. Add the following rule to the array in `config/routes.php`, preserving any rules already there:

```php
'api/navigation/main-menu' => [
    'template' => '_api/navigation-main-menu',
],
```

If the file does not exist, create it with this complete content:

```php
<?php

return [
    'api/navigation/main-menu' => [
        'template' => '_api/navigation-main-menu',
    ],
];
```

Craft’s [routing documentation](https://craftcms.com/docs/5.x/system/routing) explains how template routes are resolved. Ensure no entry or other route already owns `/api/navigation/main-menu`.

Visit that URL on your Craft site. You should see a JSON array containing your menu titles, URLs, and nested `children`. An empty menu produces `[]`. If you get a 404, check the rule, template path, and template-rendering prerequisite above. If the array is empty unexpectedly, check the handle and whether the menu is enabled on the requested site.

This endpoint is public. The default tree can include node custom fields; only use it for menu content intended for visitors. The `raw` filter is used here because the entire response is JSON, not HTML containing JSON.

## Display the Links

For this example, put the following HTML in a page on the same site. Create `web/js/menu-json.js` using the JavaScript below and load it once from that page:

```html
<nav aria-label="Menu" data-json-menu>
    <p data-menu-status>Loading menu…</p>
</nav>
<script src="/js/menu-json.js" defer></script>
```

```js
async function loadMenu() {
    const container = document.querySelector('[data-json-menu]');
    if (!container) {
        return;
    }

    const status = container.querySelector('[data-menu-status]');

    function pageKey(url) {
        const parsed = new URL(url, window.location.href);
        return parsed.origin + (parsed.pathname.replace(/\/+$/, '') || '/');
    }

    function renderNodes(nodes) {
        const list = document.createElement('ul');

        for (const node of nodes) {
            const item = document.createElement('li');
            const destination = node.url ? new URL(node.url, window.location.href) : null;
            const isLink = destination && ['http:', 'https:'].includes(destination.protocol);
            const label = document.createElement(isLink ? 'a' : 'span');
            label.textContent = node.title;

            if (isLink) {
                label.href = destination.href;
                if (pageKey(destination.href) === pageKey(window.location.href)) {
                    label.setAttribute('aria-current', 'page');
                }
                if (node.target === '_blank') {
                    label.target = '_blank';
                    label.rel = 'noopener';
                }
            }

            item.append(label);
            if (node.children?.length) {
                item.append(renderNodes(node.children));
            }
            list.append(item);
        }

        return list;
    }

    try {
        const response = await fetch('/api/navigation/main-menu');
        if (!response.ok) {
            throw new Error('Menu request failed.');
        }
        const tree = await response.json();
        if (!Array.isArray(tree)) {
            throw new Error('Unexpected menu response.');
        }
        if (tree.length === 0) {
            status.textContent = 'No menu links are available.';
            return;
        }
        container.replaceChildren(renderNodes(tree));
    } catch (error) {
        status.textContent = 'The menu could not be loaded. Please reload the page.';
        console.error(error);
    }
}

loadMenu();
```

The example builds a nested list and assigns titles with `textContent`. Structural items become text labels. It renders HTTP and HTTPS destinations; add handling for other schemes if your menu needs them. It does not apply arbitrary custom HTML attributes from the response.

For a Craft site in a subdirectory, adjust the script and fetch URLs to include its base path. A frontend on another origin also needs the full endpoint URL and a server CORS policy allowing that frontend to read the response.

## Highlight the Current Page

The endpoint’s `current`, `active`, and `hasActiveChild` flags describe the URL requested from Craft: `/api/navigation/main-menu`. Requesting that endpoint from another page on the same site does not change its URL context. Passing an arbitrary path query parameter does not change Navigation’s matching either.

The JavaScript above deliberately matches `node.url` to `window.location.href`. It ignores trailing slashes, query strings, and fragments, and marks exact page matches with `aria-current="page"`. This is a small frontend matching policy, not a reproduction of Navigation’s complete branch-matching behaviour. If your frontend uses a different origin or path structure from Craft, map menu URLs to frontend destinations before matching them. A client-side router should repeat the matching after route changes.

Add a style such as `[data-json-menu] [aria-current='page'] { font-weight: 700; }` to your page stylesheet. Open two pages represented in the menu and confirm the highlight moves between them. The endpoint can return the same tree on both requests while the browser highlights different links.

## Include Linked Element Data

Only enable linked data when your client needs it, such as entry fields for a promotional card. Replace the tree assignment in the endpoint with:

```twig
{% set tree = craft.navigation.tree('mainMenu', {
    withLinkedElements: true,
}) %}
```

This output option loads linked elements after the underlying node fetch, which can still use Navigation’s tree cache. The response includes linked element arrays, so inspect the fields before exposing them publicly. For a deliberately restricted selection of fields and schema permissions, use [GraphQL](/graphql/query-nodes).

## Control the Tree and Cache

A `level: 1` criterion selects the roots but does not remove their nested children. To limit the displayed depth, adapt the recursive renderer. Dynamic projected children appear in the response unless you use `craft.navigation.tree({ handle: 'mainMenu', withProjectedChildren: false })`.

The example starts with `Cache-Control: no-store` while you test it. Navigation’s internal tree cache is separate from HTTP caching. If you later cache the response in a CDN or a full-page cache, arrange for it to refresh when menu content changes; see [Navigation with Blitz](/user-guides/frontend-headless/navigation-with-blitz-and-full-page-cache).

## Check the Finished Result

Confirm that the endpoint returns JSON, the page displays nested links, and the current link changes on different page URLs. Temporarily use an incorrect endpoint URL to check the visible error message, then restore it. Finally, change a node title in the builder, save the menu, and reload the page to confirm the new title reaches the client.
