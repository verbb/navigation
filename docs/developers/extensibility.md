# Extensibility

You can add your own custom elements to be compatible with Navigation by using the provided events. The below shows an example of how entries are setup.

```php
use verbb\navigation\services\Elements;
use verbb\navigation\events\RegisterElementEvent;
use yii\base\Event;

Event::on(Elements::class, Elements::EVENT_REGISTER_NAVIGATION_ELEMENT, function(RegisterElementEvent $event) {
    $event->elements['entries'] = [
        'label' => Craft::t('navigation', 'Entries'),
        'button' => Craft::t('navigation', 'Add an entry'),
        'type' => 'craft\\elements\\Entry',
    ];
});
```