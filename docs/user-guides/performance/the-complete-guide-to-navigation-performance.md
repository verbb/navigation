# The Complete Guide to Navigation Performance

A header menu appears on many pages, so repeated database work in its template can affect the whole site. This guide helps you measure a menu, identify what data its design needs, and choose loading options without giving up caching unnecessarily.

Use a saved `mainMenu` rendered in a shared partial. You need access to the Twig files and Craft’s debug toolbar, or another profiler that shows database queries. Start with [Rendering Nodes](/template-guides/rendering-nodes) if you do not yet have a menu on the page. Work in development so profiling changes do not affect visitors.

## Measure the Menu

Load a page that includes the menu and record its database queries and response time. Temporarily comment out the menu include and reload. The difference helps identify the menu’s contribution, although other caches and shared queries can affect the comparison. Restore the include before continuing.

Compare both a request after a menu save and a repeated request. The first can rebuild Navigation’s tree cache; the second may reuse it. Keep the page URL, site, and content the same when comparing loading options. Change one option at a time.

The examples below describe the work each query performs rather than promising a query count. Menu size, node types, relational fields, Dynamic sources, and other page content all affect the result.

## Display Links with the Defaults

For titles and destinations, the node contains the data you need. Put this complete example in your menu partial:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).all() %}

<ul>
    {% for node in nodes %}
        <li>
            {{ node.link }}
            {% if node.children|length %}
                <ul>
                    {% for child in node.children %}
                        <li>{{ child.link }}</li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

Navigation loads the menu’s hierarchy automatically on frontend requests. The top-level query returns roots, with their child relationships available in memory. Both `node.children` and `node.children.all()` can read the loaded collection without issuing a new query per parent.

If an existing query has `withNodeHierarchy(false)`, it opts out of this loading. Without another eager-loading strategy, executing child queries can then add work for each parent. Remove that explicit opt-out when you want the default menu hierarchy, and compare the profiler results again.

Craft’s `{% nav %}` tag is another way to write recursive markup; changing a `for` loop to `{% nav %}` is not itself a database optimisation. Use the loop that suits your HTML.

## Read Fields from Linked Entries

Suppose each entry in your menu has a Plain Text field with handle `summary`, and your design shows that text beneath the link. Accessing `node.element` without a loading flag can fetch each linked entry separately.

Replace the query and output in the partial with:

```twig
{% set nodes = craft.navigation.nodes('mainMenu')
    .withLinkedElements()
    .all() %}

<ul>
    {% for node in nodes %}
        <li>
            {{ node.link }}
            {% if node.element and node.element.summary is defined %}
                <p>{{ node.element.summary }}</p>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

This example displays a flat list so you can isolate linked-entry loading. The flag batches linked elements rather than loading each through separate lazy access. It also bypasses Navigation’s tree cache for this query, so compare the full request cost, including repeated requests.

The flag loads the entry, not all of its relations. If you replace the summary with an Assets field and call `.one()` on that field for every entry, those asset queries are additional work. Do not assume linked-entry loading eliminates every query inside the loop.

When the design only needs the stored node title and URL, return to the default query and keep its cache eligibility.

## Read Menu Fields Once

A promotion above the links belongs to the menu, rather than any one node. Configure a Plain Text menu field with handle `promoHeading` using [Menu Fields](/feature-tour/menu-fields), and enter a value for the current site.

Fetch it once outside the node loop:

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

{% if menu and menu.promoHeading %}
    <p>{{ menu.promoHeading }}</p>
{% endif %}

<ul>
    {% for node in nodes %}
        <li>{{ node.link }}</li>
    {% endfor %}
</ul>
```

This separates the single menu-field read from the cacheable node query. If you instead need menu elements attached to nodes throughout a larger query, use `withMenu()` and access `node.menu`. That flag batches menus but bypasses the tree cache. Choose it because the template needs those objects, not as an automatic replacement for a single menu query.

## Combine Nested Links and Entry Fields

For a dropdown that reads linked-entry data on its children, add `withLinkedElements()` to the top-level query. Frontend hierarchy loading still happens automatically:

```twig
{% set nodes = craft.navigation.nodes('mainMenu')
    .level(1)
    .withLinkedElements()
    .all() %}

<ul>
    {% for node in nodes %}
        <li>
            {{ node.link }}
            <ul>
                {% for child in node.children %}
                    <li>
                        {{ child.link }}
                        {% if child.element and child.element.summary is defined %}
                            <p>{{ child.element.summary }}</p>
                        {% endif %}
                    </li>
                {% endfor %}
            </ul>
        </li>
    {% endfor %}
</ul>
```

Inspect both parent and child loading in the profiler. For a query used outside normal frontend requests, `withNodeHierarchy()` explicitly requests hierarchy loading. There is no need to add it to every frontend query to get the default behaviour.

## Check Cache Eligibility

Open **Navigation → Settings → Performance**. Auto is the default and caches eligible menu queries. Static uses the configured duration, Manual requires `withNavigationCache()`, and Off disables the plugin cache. See [Performance & Caching](/frontend/performance-and-caching) for the mode comparison.

Queries with `withLinkedElements()` or `withMenu()` bypass this cache, as do unsupported query shapes such as arbitrary search or ordering. Control-panel, console, and preview reads also bypass the public cache. Test performance through the frontend URL your visitors use.

The output option `craft.navigation.tree('mainMenu', { withLinkedElements: true })` is different: the node fetch happens before linked elements are loaded for serialisation. The node fetch can still use its cache. An HTTP cache storing that JSON response is another layer with its own refresh behaviour.

## Check Highlights and Fresh Content

After selecting a query, load two page URLs represented in the menu. Confirm their current and active states differ correctly. Navigation resolves those states after loading the tree, including after a cache hit.

Change a node title in the builder, save, and reload the frontend. Confirm the title updates and the highlighted link is still correct. If old HTML remains, check any full-page cache or CDN separately using [Navigation with Blitz](/user-guides/frontend-headless/navigation-with-blitz-and-full-page-cache).

Finish by recording the query and response-time measurements for your original and revised partial. Keep the change only when it improves the measured request or supplies data the design requires. A menu that already loads quickly and displays the correct content needs no extra loading flags.
