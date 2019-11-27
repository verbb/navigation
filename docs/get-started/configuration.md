# Configuration

Create an `navigation.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

```php
<?php

return [
    '*' => [
        'pluginName' => 'Navigation',
        'disabledElements' => []
    ]
];
```

### Configuration options

- `pluginName` - Optionally change the name of the plugin.
- `disabledElements` - An array of elements to disable, so they cannot be added to navs. Availble options are `assets`, `entries`, `categories`, `custom`.


## Control Panel

You can also manage configuration settings through the Control Panel by visiting Settings → Navigation.
