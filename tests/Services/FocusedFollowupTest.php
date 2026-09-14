<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;
use craft\services\Structures;
use yii\base\Event;

it('rolls back staged copies rejected on their final save or placement', function(string $failure) {
    $menu = F::menu();
    $source = F::customNode($menu, 'Source', '/source');
    $eventClass = $failure === 'save' ? Node::class : Structures::class;
    $eventName = $failure === 'save' ? Node::EVENT_BEFORE_SAVE : Structures::EVENT_BEFORE_UPDATE_ELEMENT;
    $veto = static function($event) use ($failure) {
        $node = $failure === 'save' ? $event->sender : $event->element;
        if ($node instanceof Node && $node->getIsPendingPublish() && ($failure !== 'save' || !$event->isNew)) {
            $event->isValid = false;
        }
    };
    Event::on($eventClass, $eventName, $veto);
    try {
        boundaryRequest(function() use ($menu, $source) {
            Craft::$app->request->setBodyParams(['menuId'=>$menu->id,'siteId'=>$source->siteId,'nodeIds'=>[$source->id]]);
            $response = (new verbb\navigation\controllers\BuilderController('builder',N::$plugin))->actionDuplicateNodes();
            expect($response->statusCode)->toBe(400);
            expect(Node::find()->menuId($menu->id)->status(null)->ids())->toEqual([$source->id]);
            expect(N::$plugin->getBuildSessions()->getSession($menu->id,$source->siteId))->toBeNull();
        });
    } finally { Event::off($eventClass, $eventName, $veto); }
})->with(['save','placement']);

it('counts only fully placed site copies after a veto or exception', function(string $failure) {
    $target = F::secondarySite();
    $menu = F::menu(null, verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $a = F::customNode($menu, 'Accepted', '/accepted');
    $b = F::customNode($menu, 'Rejected', '/rejected');
    $veto = static function($event) use ($target, $failure) {
        if ($event->element instanceof Node && $event->element->siteId == $target->id && $event->element->title === 'Rejected') {
            if ($failure === 'exception') throw new RuntimeException('Injected placement failure');
            $event->isValid = false;
        }
    };
    Event::on(Structures::class, Structures::EVENT_BEFORE_INSERT_ELEMENT, $veto);
    try {
        $result = N::$plugin->getNodes()->copyNodesToSite($menu->id,$a->siteId,[$a->id,$b->id],$target->id);
    } finally { Event::off(Structures::class, Structures::EVENT_BEFORE_INSERT_ELEMENT, $veto); }
    expect($result['successCount'])->toBe(1);
    expect($result['failCount'])->toBe(1);
    $copies = Node::find()->menuId($menu->id)->siteId($target->id)->status(null)->all();
    expect($copies)->toHaveCount(1);
    expect($copies[0]->title)->toBe('Accepted');
    expect($copies[0]->lft)->not->toBeNull();
    expect(Node::find()->menuId($menu->id)->siteId($a->siteId)->status(null)->ids())->toEqual([$a->id,$b->id]);
})->with(['veto','exception']);

it('rejects final sibling overflow but permits a full-tree swap at capacity', function() {
    $menu = F::menu();
    $a = F::customNode($menu,'A','/a');
    $b = F::customNode($menu,'B','/b',$a);
    $x = F::customNode($menu,'X','/x');
    $y = F::customNode($menu,'Y','/y',$x);
    $menu->maxNodesSettings = [['level'=>2,'max'=>1]];
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $before = Revision::get($menu);
    expect(fn()=>N::$plugin->getBuildSessions()->applyStructureMoves($menu,$a->siteId,
        [['elementId'=>$y->id,'parentId'=>$a->id,'prevId'=>$b->id]]))
        ->toThrow(yii\web\BadRequestHttpException::class);
    expect(Revision::get($menu))->toBe($before);
    N::$plugin->getBuildSessions()->applyStructureMoves($menu,$a->siteId,[
        ['elementId'=>$y->id,'parentId'=>$a->id,'prevId'=>null],
        ['elementId'=>$b->id,'parentId'=>$x->id,'prevId'=>null],
    ]);
    expect(Node::find()->id($y->id)->one()->getParentId())->toBe($a->id);
    expect(Node::find()->id($b->id)->one()->getParentId())->toBe($x->id);
});

it('enforces native move limits using the destination parent and subtree', function(string $action) {
    $menu = F::menu();
    $a = F::customNode($menu,'A','/a');
    $b = F::customNode($menu,'B','/b',$a);
    $x = F::customNode($menu,'X','/x');
    F::customNode($menu,'Y','/y',$x);
    $menu->maxLevels = 2;
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $before = Revision::get($menu);
    $target = $action === 'moveAfter' ? $b : $a;
    expect(Craft::$app->structures->$action($menu->structureId,$x,$target))->toBeFalse();
    expect(Revision::get($menu))->toBe($before);
    // The veto must release the mutex and allow a subsequent legal move.
    expect(Craft::$app->structures->moveAfter($menu->structureId,$x,$a))->toBeTrue();
})->with(['append','prepend','moveAfter']);

it('rolls back a native save with a parent beyond the menu depth', function() {
    $menu = F::menu();
    $a = F::customNode($menu,'A','/a');
    $b = F::customNode($menu,'B','/b');
    $menu->maxLevels = 1;
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $before = Revision::get($menu);
    $b->setParentId($a->id);
    expect(fn()=>Craft::$app->elements->saveElement($b))->toThrow(yii\base\UserException::class);
    expect(Revision::get($menu))->toBe($before);
    expect(Node::find()->id($b->id)->one()->getParentId())->toBeNull();
});

it('checks native sibling capacity against the actual destination', function(string $action) {
    $menu = F::menu();
    $a = F::customNode($menu,'A','/a');
    $b = F::customNode($menu,'B','/b',$a);
    $x = F::customNode($menu,'X','/x');
    $menu->maxNodesSettings = [['level'=>2,'max'=>1]];
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $before = Revision::get($menu);
    expect(Craft::$app->structures->$action($menu->structureId,$x,$action === 'moveAfter' ? $b : $a))->toBeFalse();
    expect(Revision::get($menu))->toBe($before);
    expect(Craft::$app->structures->appendToRoot($menu->structureId,$b))->toBeTrue();
    expect(Craft::$app->structures->append($menu->structureId,$x,$a))->toBeTrue();
    expect(Node::find()->id($x->id)->one()->getParentId())->toBe($a->id);
})->with(['append','prepend','moveAfter']);

it('does not retain a new node rejected by the native depth limit', function() {
    $menu = F::menu();
    $a = F::customNode($menu,'A','/a');
    $menu->maxLevels = 1;
    N::$plugin->getMenus()->saveMenu($menu);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    expect(fn()=>F::customNode($menu,'Rejected','/rejected',$a))->toThrow(yii\base\UserException::class);
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toEqual([$a->id]);
});

it('rolls back a copy when its final site settings cannot be saved', function() {
    $target = F::secondarySite();
    $menu = F::menu(null, verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $source = F::customNode($menu,'Source','/source');
    $veto = static function($event) use ($target) {
        if ($event->sender->siteId == $target->id) $event->isValid = false;
    };
    $recordClass = verbb\navigation\records\NodeSiteSettings::class;
    Event::on($recordClass, $recordClass::EVENT_BEFORE_UPDATE, $veto);
    try {
        $result = N::$plugin->getNodes()->copyNodesToSite($menu->id,$source->siteId,[$source->id],$target->id);
    } finally { Event::off($recordClass, $recordClass::EVENT_BEFORE_UPDATE, $veto); }
    expect($result['successCount'])->toBe(0);
    expect($result['failCount'])->toBe(1);
    expect(Node::find()->menuId($menu->id)->siteId($target->id)->status(null)->count())->toBe(0);
});
