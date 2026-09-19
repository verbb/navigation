/** Seed a populated navigation tree for the production feature screenshot. */

use craft\models\FieldLayout;
use craft\helpers\Json;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\models\Nav;
use verbb\navigation\models\Nav_SiteSettings;
use verbb\navigation\nodetypes\CustomType;

$site = Craft::$app->getSites()->getPrimarySite();
$navs = Navigation::$plugin->getNavs();
$nav = $navs->getNavByHandle('docsScreenshotMainMenu');

if (!$nav) {
    $nav = new Nav([
        'name' => 'Main Menu',
        'handle' => 'docsScreenshotMainMenu',
        'instructions' => 'Arrange links into the hierarchy used by the site.',
        'maxLevels' => 3,
        'permissions' => [],
        'showSiteMenu' => true,
    ]);
    $siteSettings = new Nav_SiteSettings(['siteId' => $site->id, 'enabled' => true]);
    $nav->setSiteSettings([$site->id => $siteSettings]);
    $nav->setFieldLayout(new FieldLayout(['type' => Node::class]));
    if (!$navs->saveNav($nav)) throw new RuntimeException('Unable to save navigation: ' . Json::encode($nav->getErrors()));
    $nav = $navs->getNavByHandle('docsScreenshotMainMenu');
}

$existing = Node::find()->navId($nav->id)->siteId($site->id)->status(null)->all();
if (!$existing) {
    $createNode = static function(string $title, string $url, ?Node $parent = null) use ($nav, $site): Node {
        $node = new Node([
            'navId' => $nav->id,
            'siteId' => $site->id,
            'type' => CustomType::class,
            'title' => $title,
            'enabled' => true,
        ]);
        $node->url = $url;
        $node->setParentId($parent?->id);
        if (!Craft::$app->getElements()->saveElement($node)) throw new RuntimeException('Unable to save navigation node: ' . Json::encode($node->getErrors()));
        return $node;
    };

    $gin = $createNode('Gin', '/spirits/gin');
    $createNode("Hendrick's Gin", '/spirits/gin/hendricks', $gin);
    $createNode('Bombay Sapphire London Dry Gin', '/spirits/gin/bombay-sapphire', $gin);
    $createNode('Tanqueray London Dry Gin', '/spirits/gin/tanqueray', $gin);
    $createNode('Forty Spotted', '/spirits/gin/forty-spotted', $gin);
    $createNode('Four Pillars', '/spirits/gin/four-pillars', $gin);

    $vodka = $createNode('Vodka', '/spirits/vodka');
    $createNode('Absolut', '/spirits/vodka/absolut', $vodka);
    $createNode('Smirnoff', '/spirits/vodka/smirnoff', $vodka);
    $createNode('Svedka', '/spirits/vodka/svedka', $vodka);

    $whisky = $createNode('Whisky', '/spirits/whisky');
    $createNode('Johnnie Walker', '/spirits/whisky/johnnie-walker', $whisky);
    $createNode('Lark Limited', '/spirits/whisky/lark', $whisky);
    $createNode('Sullivans Cove', '/spirits/whisky/sullivans-cove', $whisky);
}

echo Json::encode(['buildRoute' => '/admin/navigation/navs/build/' . $nav->id], JSON_THROW_ON_ERROR);
