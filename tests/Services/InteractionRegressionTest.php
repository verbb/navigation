<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;
use yii\base\Event;

it('supports observing modifying and cancelling copy hooks', function(string $mode) {
    $target=F::secondarySite();
    $menu=F::menu(null,verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $source=F::customNode($menu,'Source','/source');
    $hook=static function($event)use($mode){
        if($mode==='modify')$event->targetSettings->url='/modified';
        if($mode==='cancel')$event->isValid=false;
    };
    $class=verbb\navigation\services\Nodes::class;
    Event::on($class,$class::EVENT_BEFORE_COPY_NODE_TO_SITE,$hook);
    try{$result=N::$plugin->getNodes()->copyNodesToSite($menu->id,$source->siteId,[$source->id],$target->id);}
    finally{Event::off($class,$class::EVENT_BEFORE_COPY_NODE_TO_SITE,$hook);}
    $copies=Node::find()->menuId($menu->id)->siteId($target->id)->status(null)->all();
    expect($result['successCount'])->toBe($mode==='cancel'?0:1);
    expect($result['failCount'])->toBe($mode==='cancel'?1:0);
    expect($copies)->toHaveCount($mode==='cancel'?0:1);
    if($copies)expect($copies[0]->getRawUrl())->toBe($mode==='modify'?'/modified':'/source');
    expect(Node::find()->id($source->id)->siteId($source->siteId)->one()->getRawUrl())->toBe('/source');
})->with(['observe','modify','cancel']);

it('rolls back native edits when node or site records reject persistence', function(string $record) {
    $menu=F::menu();
    $node=F::customNode($menu,'Before','/before');
    $class=$record==='site'?verbb\navigation\records\NodeSiteSettings::class:verbb\navigation\records\Node::class;
    $veto=static function($event)use($node,$record){
        if(($record==='site'?$event->sender->nodeId:$event->sender->id)==$node->id)$event->isValid=false;
    };
    Event::on($class,$class::EVENT_BEFORE_UPDATE,$veto);
    try{
        $node->title='After';$node->setUrl('/after');$node->classes='changed';
        expect(fn()=>Craft::$app->elements->saveElement($node))->toThrow(yii\base\UserException::class);
    }finally{Event::off($class,$class::EVENT_BEFORE_UPDATE,$veto);}
    $fresh=Node::find()->id($node->id)->one();
    expect($fresh->title)->toBe('Before');
    expect($fresh->getRawUrl())->toBe('/before');
    expect($fresh->classes)->not->toBe('changed');
    $fresh->title='Saved';$fresh->setUrl('/saved');
    expect(Craft::$app->elements->saveElement($fresh))->toBeTrue();
    expect(Node::find()->id($node->id)->one()->getRawUrl())->toBe('/saved');
})->with(['site','node']);

it('rolls back publication when promoted children exceed root capacity', function(bool $withMoves) {
    $menu=F::menu();
    $a=F::customNode($menu,'Parent','/parent');
    $b=F::customNode($menu,'B','/b',$a);
    $c=F::customNode($menu,'C','/c',$a);
    $x=F::customNode($menu,'X','/x');
    $menu->maxNodesSettings=[['level'=>1,'max'=>2]];
    N::$plugin->getMenus()->saveMenu($menu);
    $menu=N::$plugin->getMenus()->getMenuById($menu->id);
    $sessions=N::$plugin->getBuildSessions();
    $session=$sessions->getOrCreate($menu->id,$a->siteId);
    $sessions->stageDelete($session,$a);
    $before=Revision::get($menu);
    $moves=$withMoves?[
        ['elementId'=>$b->id,'parentId'=>$a->id,'prevId'=>null],
        ['elementId'=>$c->id,'parentId'=>$a->id,'prevId'=>$b->id],
    ]:null;
    expect(fn()=>$sessions->publish($session,$withMoves,$moves))->toThrow(yii\web\BadRequestHttpException::class);
    expect(Revision::get($menu))->toBe($before);
    expect(Node::find()->id($a->id)->status(null)->one()->getIsPendingDelete())->toBeTrue();
    expect($sessions->getSession($menu->id,$a->siteId)->stagedDeletes)->toHaveCount(1);
})->with([false,true]);

it('validates publication after the entire deletion batch', function() {
    $menu=F::menu();
    $a=F::customNode($menu,'Parent','/parent');
    $b=F::customNode($menu,'B','/b',$a);
    $c=F::customNode($menu,'C','/c',$a);
    $x=F::customNode($menu,'X','/x');
    $menu->maxNodesSettings=[['level'=>1,'max'=>2]];
    N::$plugin->getMenus()->saveMenu($menu);
    $sessions=N::$plugin->getBuildSessions();
    $session=$sessions->getOrCreate($menu->id,$a->siteId);
    $sessions->stageDelete($session,$a);
    $sessions->stageDelete($session,$x);
    // Removing A alone exceeds capacity, but removing X completes a valid tree.
    $result=$sessions->publish($session);
    expect($result['deletedCount'])->toBe(2);
    expect(Node::find()->menuId($menu->id)->level(1)->status(null)->ids())->toEqual([$b->id,$c->id]);
    expect($sessions->getSession($menu->id,$a->siteId))->toBeNull();
});

it('checks native child promotion before committing deletion', function(bool $hard) {
    $menu=F::menu();
    $a=F::customNode($menu,'Parent','/parent');
    F::customNode($menu,'B','/b',$a);
    F::customNode($menu,'C','/c',$a);
    F::customNode($menu,'X','/x');
    $menu->maxNodesSettings=[['level'=>1,'max'=>2]];
    N::$plugin->getMenus()->saveMenu($menu);
    $menu=N::$plugin->getMenus()->getMenuById($menu->id);
    $before=Revision::get($menu);
    expect(fn()=>Craft::$app->elements->deleteElement($a,$hard))->toThrow(yii\web\BadRequestHttpException::class);
    expect(Revision::get($menu))->toBe($before);
    expect(Node::find()->id($a->id)->one())->not->toBeNull();
})->with([false,true]);
