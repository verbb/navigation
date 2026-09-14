<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;

it('keeps rejected restores trashed and permits valid restores', function(string $location) {
    $menu=F::menu();
    $parent=$location==='parent'?F::customNode($menu,'Parent','/parent'):null;
    $node=F::customNode($menu,'Original','/original',$parent);
    Craft::$app->elements->deleteElement($node);
    $replacement=F::customNode($menu,'Replacement','/replacement',$parent);
    $menu->maxNodesSettings=[['level'=>$parent?2:1,'max'=>1]];
    N::$plugin->getMenus()->saveMenu($menu);
    $menu=N::$plugin->getMenus()->getMenuById($menu->id);
    $before=Revision::get($menu);
    $trashed=Node::find()->id($node->id)->trashed(true)->status(null)->one();
    expect(fn()=>Craft::$app->elements->restoreElement($trashed))->toThrow(yii\base\UserException::class);
    expect(Node::find()->id($node->id)->status(null)->one())->toBeNull();
    expect(Revision::get($menu))->toBe($before);
    expect(Craft::$app->elements->deleteElement($replacement))->toBeTrue();
    $trashed=Node::find()->id($node->id)->trashed(true)->status(null)->one();
    expect(Craft::$app->elements->restoreElement($trashed))->toBeTrue();
    $restored=Node::find()->id($node->id)->one();
    expect($restored->lft)->not->toBeNull();
    expect($restored->getParentId())->toBe($parent?->id);
})->with(['root','parent']);

it('restores at root when the original parent is gone', function() {
    $menu=F::menu();
    $parent=F::customNode($menu,'Parent','/parent');
    $node=F::customNode($menu,'Child','/child',$parent);
    Craft::$app->elements->deleteElement($node);
    Craft::$app->elements->deleteElement($parent,true);
    $trashed=Node::find()->id($node->id)->trashed(true)->status(null)->one();
    expect(Craft::$app->elements->restoreElement($trashed))->toBeTrue();
    $restored=Node::find()->id($node->id)->one();
    expect($restored->lft)->not->toBeNull();
    expect($restored->getParentId())->toBeNull();
});

it('guards native move actions with current persisted site grants', function() {
    $secondary=F::secondarySite();
    $primary=Craft::$app->sites->getPrimarySite();
    $menu=F::menu(null,verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $allowed=F::customNode($menu,'Allowed','/allowed');
    $denied=F::customNode($menu,'Denied','/denied',null,$secondary->id);
    $user=new craft\elements\User(['username'=>uniqid('editor'),'email'=>uniqid('editor').'@example.test']);
    Craft::$app->elements->saveElement($user);
    Craft::$app->set('userPermissions',new craft\services\UserPermissions());
    Craft::$app->userPermissions->saveUserPermissions($user->id,['accessCp','navigation-manageMenu:'.$menu->uid,'editSite:'.$primary->uid]);
    boundaryRequest(function()use($menu,$primary,$secondary,$allowed,$denied,$user){
        Craft::$app->user->setIdentity($user);
        $controller=new craft\controllers\StructuresController('structures',Craft::$app);
        $event=fn()=>new yii\base\ActionEvent($controller->createAction('move-element'));
        Craft::$app->request->setBodyParams(['structureId'=>$menu->structureId,'elementId'=>$allowed->id,'siteId'=>$primary->id]);
        $controller->trigger($controller::EVENT_BEFORE_ACTION,$event());
        expect($allowed->canSave($user))->toBeTrue();
        Craft::$app->request->setBodyParams(['structureId'=>$menu->structureId,'elementId'=>$denied->id,'siteId'=>$secondary->id]);
        expect(fn()=>$controller->trigger($controller::EVENT_BEFORE_ACTION,$event()))->toThrow(yii\web\ForbiddenHttpException::class);
        // An old structure capability must not survive a menu permission revocation.
        Craft::$app->userPermissions->saveUserPermissions($user->id,['accessCp','editSite:'.$primary->uid]);
        Craft::$app->request->setBodyParams(['structureId'=>$menu->structureId,'elementId'=>$allowed->id,'siteId'=>$primary->id]);
        expect(fn()=>$controller->trigger($controller::EVENT_BEFORE_ACTION,$event()))->toThrow(yii\web\ForbiddenHttpException::class);
    });
});

it('rejects native moves that carry another owners pending descendant', function() {
    $menu=F::menu();
    $parent=F::customNode($menu,'Parent','/parent');
    $child=F::customNode($menu,'Pending','/pending',$parent);
    $child->setPendingPublish(true);$child->enabled=false;
    Craft::$app->elements->saveElement($child);
    $owner=new craft\elements\User(['username'=>uniqid('owner'),'email'=>uniqid('owner').'@example.test']);
    Craft::$app->elements->saveElement($owner);
    $sessions=N::$plugin->getBuildSessions();
    $sessions->stageAddedNodes($sessions->getOrCreate($menu->id,$parent->siteId,$owner->id),[$child->id]);
    boundaryRequest(function()use($menu,$parent){
        Craft::$app->request->setBodyParams(['structureId'=>$menu->structureId,'elementId'=>$parent->id,'siteId'=>$parent->siteId]);
        $controller=new craft\controllers\StructuresController('structures',Craft::$app);
        expect(fn()=>$controller->trigger($controller::EVENT_BEFORE_ACTION,new yii\base\ActionEvent($controller->createAction('move-element'))))
            ->toThrow(yii\web\ForbiddenHttpException::class);
    });
});


it('restores a secondary-site node beneath its original site-local parent', function() {
    $site=F::secondarySite();
    $menu=F::menu(null,verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $parent=F::customNode($menu,'Parent','/parent',null,$site->id);
    $node=F::customNode($menu,'Child','/child',$parent,$site->id);
    Craft::$app->elements->deleteElement($node);
    $trashed=Node::find()->id($node->id)->siteId($site->id)->trashed(true)->status(null)->one();
    expect(Craft::$app->elements->restoreElement($trashed))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($site->id)->one()->getParentId())->toBe($parent->id);
});
