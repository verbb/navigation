# Configuration

Create an `navigation.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

```php
<?php

return [
    '*' => [
        'pluginName' => 'Navigation',
        'bypassProjectConfig' => false,
        'propagateSiteElements' => true,
    ]
];
```

### Configuration options

- `pluginName` - Optionally change the name of the plugin.
- `bypassProjectConfig` - Prevents navigations from being saved to Project Config. Be sure you know what you're doing with this!
- `propagateSiteElements` - With propagation set to true for a navigation, auto-select the site-specific equivalient of a node for each site's navigation. Set this to `false` to ensure nodes of the chosen site are the same across all your multi-site menus.


## Control Panel

You can also manage configuration settings through the Control Panel by visiting Settings → Navigation.
