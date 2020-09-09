# Eager-Loading
Craft features a concept called [Eager-Loading](https://craftcms.com/docs/3.x/dev/eager-loading-elements.html), allowing some significant performance benefits when dealing with elements.

We can make use of this too, to speed up rendering of navigation nodes. However, you'll only really see benefits from eager-loading when your navigation have multiple levels. A single level navigation won't get any benefit from eager-loading.

### craft.navigation.render()
If you're using the `craft.navigation.render()` Twig function, there's nothing you need to do! Navigation eager-loads nested navigations automatically.

### craft.navigation.nodes()
Let's take a look at an example navigation setup. We have the following navigation structure, consisting of 3-levels of nodes.

```
- Node 1
    - Node 1-1
    - Node 1-2
    - Node 1-3
    - Node 1-4
- Node 2
- Node 3
    - Node 3-1
    - Node 3-2
    - Node 3-3
- Node 4
- Node 5
- Node 6
    - Node 6-1
    - Node 6-2
    - Node 6-3
- Node 7
- Node 8
- Node 9
- Node 10
```

And we'll use the following Twig to output the nodes:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).all() %}

{% for node in nodes %}
    {{ node.link }}

    {% for subnode in node.children.all() %}
        {{ subnode.link }}
    {% endfor %}
{% endfor %}
```

Whilst this will work fine, we're also producing a lot of database queries. The above should generate close to **32 queries** to fetch nested nodes. We can improve this with eager-loading the children and descendants.

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).with(['children']).all() %}

{% for node in nodes %}
    {{ node.link }}

    {% for subnode in node.children %}
        {{ subnode.link }}
    {% endfor %}
{% endfor %}
```

There's two main things to note here, we're using `with(['children'])` in our query, and we're not using `all()` to loop through sub nodes. This will bring our query count down to **10 queries** - a vast improvement over the former template.

If you have a third-level in your navigation, you'll need to eager-load those to, and so on - depending on how many levels your navigation has.

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).with(['children.children']).all() %}

{% for node in nodes %}
    {{ node.link }}

    {% for subnode in node.children %}
        {{ subnode.link }}

        {% for innernode in subnode.children %}
            {{ innernode.link }}
        {% endfor %}
    {% endfor %}
{% endfor %}
```