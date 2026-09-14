<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\elementactions\Restore;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;

it('preserves a restored three-level hierarchy in either selection order', function(bool $childFirst, bool $native) {
    $site=F::secondarySite();
    $menu=F::menu(null,verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $parent=F::customNode($menu,'Parent','/parent',null,$site->id);
    $child=F::customNode($menu,'Child','/child',$parent,$site->id);
    $leaf=F::customNode($menu,'Leaf','/leaf',$child,$site->id);
    foreach ([$leaf,$child,$parent] as $node) {
        expect(Craft::$app->elements->deleteElement($node))->toBeTrue();
    }
    // Only one root is allowed: a temporary child-at-root placement must not reject this valid tree.
    $menu->maxNodesSettings=[['level'=>1,'max'=>1]];
    N::$plugin->getMenus()->saveMenu($menu);
    $ids=$childFirst?[$leaf->id,$child->id,$parent->id]:[$parent->id,$child->id,$leaf->id];
    $query=Node::find()->id($ids)->fixedOrder()->siteId($site->id)->trashed(true)->status(null);
    $calls=[];
    $handler=function($event)use(&$calls){$calls[]=$event->sender->id;};
    yii\base\Event::on(Node::class,Node::EVENT_AFTER_RESTORE,$handler);
    try {
        if ($native) {
            boundaryRequest(function()use($query){
                $action=Craft::$app->elements->createAction(['type'=>Restore::class,'elementType'=>Node::class]);
                expect($action->performAction($query))->toBeTrue();
            });
        } else {
            expect(Craft::$app->elements->restoreElements($query->all()))->toBeTrue();
        }
    } finally {
        yii\base\Event::off(Node::class,Node::EVENT_AFTER_RESTORE,$handler);
    }
    expect($calls)->toBe($ids);
    expect(Node::find()->id($parent->id)->siteId($site->id)->one()->getParentId())->toBeNull();
    expect(Node::find()->id($child->id)->siteId($site->id)->one()->getParentId())->toBe($parent->id);
    expect(Node::find()->id($leaf->id)->siteId($site->id)->one()->getParentId())->toBe($child->id);
})->with([false,true])->with([false,true]);

it('does not restore an unselected parent or later undo a deliberate move', function() {
    $menu=F::menu();
    $parent=F::customNode($menu,'Parent','/parent');
    $child=F::customNode($menu,'Child','/child',$parent);
    $other=F::customNode($menu,'Other','/other');
    Craft::$app->elements->deleteElement($child);
    Craft::$app->elements->deleteElement($parent);
    expect(Craft::$app->elements->restoreElement(Node::find()->id($child->id)->trashed(true)->status(null)->one()))->toBeTrue();
    $child=Node::find()->id($child->id)->one();
    expect($child->getParentId())->toBeNull();
    expect(Node::find()->id($parent->id)->one())->toBeNull();
    expect(Craft::$app->structures->append($menu->structureId,$child,$other))->toBeTrue();
    expect(Craft::$app->elements->restoreElement(Node::find()->id($parent->id)->trashed(true)->status(null)->one()))->toBeTrue();
    expect(Node::find()->id($child->id)->one()->getParentId())->toBe($other->id);
});

it('rolls back every restored node when a bulk restore exceeds capacity', function(bool $native) {
    $menu=F::menu();
    $parent=F::customNode($menu,'Parent','/parent');
    $child=F::customNode($menu,'Child','/child',$parent);
    foreach ([$child,$parent] as $node) Craft::$app->elements->deleteElement($node);
    F::customNode($menu,'Replacement','/replacement');
    $menu->maxNodesSettings=[['level'=>1,'max'=>1]];
    N::$plugin->getMenus()->saveMenu($menu);
    $before=Revision::get($menu);
    $query=Node::find()->id([$child->id,$parent->id])->fixedOrder()->trashed(true)->status(null);
    if ($native) {
        boundaryRequest(function()use($query){
            $action=Craft::$app->elements->createAction(['type'=>Restore::class,'elementType'=>Node::class]);
            expect(fn()=>$action->performAction($query))->toThrow(yii\base\UserException::class);
        });
    } else {
        expect(fn()=>Craft::$app->elements->restoreElements($query->all()))->toThrow(yii\base\UserException::class);
    }
    expect(Node::find()->id([$child->id,$parent->id])->status(null)->count())->toBe(0);
    expect((int)Node::find()->id([$child->id,$parent->id])->trashed(true)->status(null)->count())->toBe(2);
    expect(Revision::get($menu))->toBe($before);
})->with([false,true]);

it('keeps unpermitted nodes trashed when using the restore action', function() {
    $allowedMenu=F::menu();$deniedMenu=F::menu();
    $allowed=F::customNode($allowedMenu,'Allowed','/allowed');
    $denied=F::customNode($deniedMenu,'Denied','/denied');
    foreach ([$allowed,$denied] as $node) Craft::$app->elements->deleteElement($node);
    $user=new craft\elements\User(['username'=>uniqid('restore'),'email'=>uniqid('restore').'@example.test']);
    Craft::$app->elements->saveElement($user);
    Craft::$app->set('userPermissions',new craft\services\UserPermissions());
    Craft::$app->userPermissions->saveUserPermissions($user->id,['accessCp','navigation-manageMenu:'.$allowedMenu->uid,'editSite:'.Craft::$app->sites->getPrimarySite()->uid]);
    boundaryRequest(function()use($allowed,$denied,$user){
        Craft::$app->user->setIdentity($user);
        $action=Craft::$app->elements->createAction(['type'=>Restore::class,'elementType'=>Node::class]);
        expect($action->performAction(Node::find()->id([$denied->id,$allowed->id])->fixedOrder()->trashed(true)->status(null)))->toBeTrue();
        expect($action->performAction(Node::find()->id($denied->id)->trashed(true)->status(null)))->toBeFalse();
    });
    expect(Node::find()->id($allowed->id)->one())->not->toBeNull();
    expect(Node::find()->id($denied->id)->one())->toBeNull();
});

it('leaves the whole restore selection trashed when a restore callback vetoes it', function() {
    $menu=F::menu();
    $parent=F::customNode($menu,'Parent','/parent');
    $child=F::customNode($menu,'Child','/child',$parent);
    foreach ([$child,$parent] as $node) Craft::$app->elements->deleteElement($node);
    $handler=function($event)use($parent){if($event->sender->id===$parent->id)$event->isValid=false;};
    yii\base\Event::on(Node::class,Node::EVENT_BEFORE_RESTORE,$handler);
    try {
        boundaryRequest(function()use($parent,$child){
            $action=Craft::$app->elements->createAction(['type'=>Restore::class,'elementType'=>Node::class]);
            expect($action->performAction(Node::find()->id([$child->id,$parent->id])->fixedOrder()->trashed(true)->status(null)))->toBeFalse();
        });
    } finally {
        yii\base\Event::off(Node::class,Node::EVENT_BEFORE_RESTORE,$handler);
    }
    expect(Node::find()->id([$child->id,$parent->id])->status(null)->count())->toBe(0);
});
